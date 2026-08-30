<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Project;
use App\Models\Mold;
use App\Models\ComponentCategory;
use App\Models\Component;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;

class ProjectManagementPage extends Page implements HasForms, HasActions
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithActions;
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Detail Project';
    protected static ?string $title = 'Detail Project';
    protected static ?string $navigationLabel = 'Overview Detail Project';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.project-management-page';

    public ?int $selectedProjectId = null;
    public ?int $selectedMoldId = null;
    public ?int $selectedCategoryId = null;
    public string $searchQuery = '';

    public function selectProject($projectId)
    {
        $this->selectedProjectId = $projectId;
        $this->selectedMoldId = null;
        $this->selectedCategoryId = null;
        $this->searchQuery = '';
    }

    public function selectMold($moldId)
    {
        $this->selectedMoldId = $moldId;
        $this->selectedCategoryId = null;
        $this->searchQuery = '';
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategoryId = $categoryId;
        $this->searchQuery = '';
    }

    public function goBack()
    {
        if ($this->selectedCategoryId) {
            $this->selectedCategoryId = null;
        } elseif ($this->selectedMoldId) {
            $this->selectedMoldId = null;
        } else {
            $this->selectedProjectId = null;
        }
        $this->searchQuery = '';
    }

    // Getters for view
    public function getProjectsProperty()
    {
        $query = Project::withCount('molds');
        if ($this->searchQuery) {
            $query->where('code', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('customer', 'like', '%' . $this->searchQuery . '%');
        }
        return $query->get()->map(function($project) {
            $project->components_count = Component::whereHas('mold', function($q) use ($project) {
                $q->where('project_id', $project->id);
            })->count();
            
            // Cost Control (Based on Uploaded Components)
            $project->total_cost = Component::whereHas('mold', function($q) use ($project) {
                $q->where('project_id', $project->id);
            })->sum(\DB::raw('stock * unit_price'));
            
            return $project;
        });
    }

    public function getSelectedProjectProperty()
    {
        return Project::find($this->selectedProjectId);
    }

    public function getMoldsProperty()
    {
        if (!$this->selectedProjectId) return collect();
        $query = Mold::where('project_id', $this->selectedProjectId);
        if ($this->searchQuery) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('name', 'like', '%' . $this->searchQuery . '%');
            });
        }
        return $query->get()->map(function($mold) {
            $mold->categories_count = Component::where('mold_id', $mold->id)->distinct('category_id')->count('category_id');
            $mold->components_count = Component::where('mold_id', $mold->id)->count();
            $mold->cost = Component::where('mold_id', $mold->id)->sum(\DB::raw('stock * unit_price'));
            return $mold;
        });
    }

    public function getSelectedMoldProperty()
    {
        return Mold::find($this->selectedMoldId);
    }

    public function getCategoriesProperty()
    {
        if (!$this->selectedMoldId) return collect();
        $categoryIds = Component::where('mold_id', $this->selectedMoldId)
                                ->whereNotNull('category_id')
                                ->pluck('category_id')
                                ->unique();
        return ComponentCategory::whereIn('id', $categoryIds)->get()->map(function($cat) {
            $cat->components_count = Component::where('mold_id', $this->selectedMoldId)
                                              ->where('category_id', $cat->id)
                                              ->count();
            $cat->sample_components = Component::where('mold_id', $this->selectedMoldId)
                                               ->where('category_id', $cat->id)
                                               ->limit(3)
                                               ->pluck('name');
            return $cat;
        });
    }
    
    public function getSelectedCategoryProperty()
    {
        return ComponentCategory::find($this->selectedCategoryId);
    }

    public function getProjectFinancialsProperty()
    {
        if (!$this->selectedProjectId) return null;
        
        $project = $this->selectedProject;
        
        // Actual cost = Total sum of used components (taken_qty * unit_price)
        $actualCost = \DB::table('components')
            ->join('molds', 'components.mold_id', '=', 'molds.id')
            ->join('stock_movements', 'components.id', '=', 'stock_movements.component_id')
            ->where('molds.project_id', $project->id)
            ->where('stock_movements.type', 'out')
            ->where('stock_movements.status', 'approved')
            ->sum(\DB::raw('stock_movements.quantity * components.unit_price'));

        $totalMolds = Mold::where('project_id', $project->id)->count();
        $totalComponents = Component::whereHas('mold', function($q) use ($project) {
            $q->where('project_id', $project->id);
        })->count();
        
        $totalCategories = Component::whereHas('mold', function($q) use ($project) {
            $q->where('project_id', $project->id);
        })->distinct('category_id')->count('category_id');

        return [
            'budget' => $project->budget,
            'actual_cost' => $actualCost,
            'variance' => $project->budget - $actualCost,
            'total_molds' => $totalMolds,
            'total_components' => $totalComponents,
            'total_categories' => $totalCategories,
        ];
    }

    public function getProjectCostByCategoryProperty()
    {
        if (!$this->selectedProjectId) return collect();

        $project = $this->selectedProject;
        
        $costByCategory = \DB::table('components')
            ->join('molds', 'components.mold_id', '=', 'molds.id')
            ->join('stock_movements', 'components.id', '=', 'stock_movements.component_id')
            ->where('molds.project_id', $project->id)
            ->where('stock_movements.type', 'out')
            ->where('stock_movements.status', 'approved')
            ->select('components.category_id', \DB::raw('SUM(stock_movements.quantity * components.unit_price) as cost'))
            ->groupBy('components.category_id')
            ->get()
            ->map(function($item) {
                $categoryName = $item->category_id ? \App\Models\ComponentCategory::find($item->category_id)->name ?? 'Uncategorized' : 'Uncategorized';
                return [
                    'category' => $categoryName,
                    'cost' => (float) $item->cost
                ];
            })
            ->sortByDesc('cost')
            ->values();

        return $costByCategory;
    }

    public function getProjectComponentsGroupedByCategoryProperty()
    {
        if (!$this->selectedProjectId) return collect();

        $project = $this->selectedProject;
        
        $grouped = Component::whereHas('mold', function($q) use ($project) {
                $q->where('project_id', $project->id);
            })
            ->with(['category', 'mold'])
            ->get()
            ->groupBy(function($item) {
                return $item->category_id ? $item->category->name : 'Uncategorized';
            });
            
        return $grouped;
    }

    public function getComponentsProperty()
    {
        if (!$this->selectedMoldId || !$this->selectedCategoryId) return collect();
        $query = Component::where('mold_id', $this->selectedMoldId)
                          ->where('category_id', $this->selectedCategoryId);
        if ($this->searchQuery) {
            $query->where('name', 'like', '%' . $this->searchQuery . '%');
        }
        return $query->get();
    }
    
    
    public function getTotalProjectsProperty()
    {
        return Project::count();
    }
    
    public function getTotalMoldsProperty()
    {
        return Mold::count();
    }

    protected function getActions(): array
    {
        return [
            Action::make('createProject')
                ->label('Tambah Project')
                ->icon('heroicon-o-plus')
                ->form([
                    TextInput::make('code')->label('Kode Project')->required()->unique('projects', 'code'),
                    TextInput::make('name')->label('Nama Project')->required(),
                    TextInput::make('customer')->label('Customer'),
                    DatePicker::make('start_date')->label('Tanggal Mulai'),
                    DatePicker::make('end_date')->label('Tanggal Selesai'),
                    TextInput::make('budget')->label('Budget')->numeric(),
                ])
                ->action(function (array $data) {
                    Project::create($data);
                }),
            Action::make('createMold')
                ->label('Tambah Mold')
                ->icon('heroicon-o-plus')
                ->form([
                    TextInput::make('code')->label('No. Mold')->required()->unique('molds', 'code'),
                    TextInput::make('name')->label('Nama Mold')->required(),
                    Select::make('project_id')
                        ->label('Project')
                        ->options(Project::pluck('code', 'id'))
                        ->default(fn () => $this->selectedProjectId)
                        ->required(),
                ])
                ->action(function (array $data) {
                    Mold::create($data);
                }),
        ];
    }
}
