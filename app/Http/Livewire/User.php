<?php

namespace App\Http\Livewire;

use App\Exports\UsersExport;
use App\Models\User as ModelsUser;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class User extends Component
{
    use WithPagination;
    use LivewireAlert;
    protected $paginationTheme = 'bootstrap';

    public $rows_number = 10;
    public $search;
    public $sort_field = 'id';
    public $sort_direction = 'asc';
    public $style_sort_direction = 'sorting_asc';

    public $branch_id = null;
    public $user_status = null;
    public $user_type = null;
    public $filters = [];
    public $paginate_ids = [];

    public function getUsers()
    {
        $this->user_status == 'all' ? $this->user_status = null : null;
        $this->user_type == 'all' ? $this->user_type = null : null;
        $this->branch_id == 'all' ? $this->branch_id = null : null;

        $this->filters['search'] = $this->search;
        $this->filters['user_type'] = $this->user_type;
        $this->filters['user_status'] = $this->user_status;
        $this->filters['branch_id'] = $this->branch_id;

        $models = ModelsUser::data()->filters($this->filters)->whereNot('user_type', 'superadmin')->whereNot('id', auth()->id())->reorder($this->sort_field, $this->sort_direction);

        if ($this->rows_number == 'all') {
            $this->rows_number = $models->count();
        }

        $data = $models->paginate($this->rows_number);

        $this->paginate_ids = $data->pluck('id')->toArray();

        return $data;
    }

    public function sortBy($field)
    {
        if ($this->sort_field == $field) {
            if ($this->sort_direction === 'asc') {
                $this->sort_direction = 'desc';
                $this->style_sort_direction = 'sorting_desc';
            } else {
                $this->sort_direction = 'asc';
                $this->style_sort_direction = 'sorting_asc';
            }
        } else {
            $this->sort_direction = 'asc';
            $this->style_sort_direction = 'sorting_asc';
        }

        $this->sort_field = $field;
    }

    public function changeUserStatus($user_id)
    {
        $user = ModelsUser::find($user_id);

        $branches = $user->branches->count();
        if (!$branches) {
            $this->alert('success', '????????????', [
                'toast' => true,
                'position' => 'center',
                'timer' => 6000,
                'text' => ' ???? ???????? ?????????? ?????????????????? ???????? ?????? ???????? ?????????????? ???? ????????',
                'timerProgressBar' => true,
            ]);
            return false;
        }

        if ($user) {
            if ($user->user_status == 'active') {
                $user->update(['user_status' => 'inactive']);
                $this->alert('warning', '????????????', [
                    'toast' => true,
                    'position' => 'center',
                    'timer' => 6000,
                    'text' => ' ???? ?????????? ?????????? ????????????????',
                    'timerProgressBar' => true,
                ]);
            } else {
                $user->update(['user_status' => 'active']);
                $this->alert('success', '', [
                    'toast' => true,
                    'position' => 'center',
                    'timer' => 6000,
                    'text' => ' ???? ?????????? ???????????????? ??????????',
                    'timerProgressBar' => true,
                ]);
            }
        }
    }

    public function render()
    {
        $users = $this->getUsers();

        if ($users->count() < 8) {
            $this->resetPage();
        }
        return view('livewire.user', [
            'users' => $users
        ]);
    }

    public function export($type)
    {
        if ($type == 'excel') {
            $excel = Excel::download(new UsersExport($this->filters, $this->sort_field, $this->sort_direction, $this->rows_number, $this->paginate_ids), 'users.xlsx');

            $this->alert('success', '', [
                'toast' => true,
                'position' => 'center',
                'timer' => 6000,
                'text' => '???? ?????????? ?????????? ??????????',
                'timerProgressBar' => true,
            ]);

            return $excel;
        }
    }
}
