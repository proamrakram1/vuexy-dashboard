<?php

namespace App\Http\Livewire;

use App\Exports\OrdersExport;
use App\Models\Order as ModelsOrder;
use App\Models\OrderEditor;
use App\Models\User;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Order extends Component
{
    use LivewireAlert;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['updateOrders', 'refreshComponent' => '$refresh'];
    public $rows_number = 10;

    public $search = '';
    public $sort_field = 'id';
    public $sort_direction = 'asc';
    public $style_sort_direction = 'sorting_asc';

    public $order_status_id = null;
    public $property_type_id = null;
    public $city_id = null;
    public $branch_type_id = null;
    public $date_from = null;
    public $date_to = null;
    public $filters = [];
    public $paginate_ids = [];

    public function updateOrders()
    {
        $this->emit('refreshComponent');
        $this->date_from = ModelsOrder::min('created_at');
        $this->date_to = ModelsOrder::max('created_at');
        $this->filters['date_from'] = $this->date_from;
        $this->filters['date_to'] = $this->date_to;
    }

    public function mount()
    {
        $this->date_from = ModelsOrder::min('created_at');
        $this->date_to = ModelsOrder::max('created_at');
        $this->filters['date_from'] = $this->date_from;
        $this->filters['date_to'] = $this->date_to;
    }

    public function getMainOrders()
    {
        $this->order_status_id == 'all' ? $this->order_status_id = null : null;
        $this->property_type_id == 'all' ? $this->property_type_id = null : null;
        $this->city_id == 'all' ? $this->city_id = null : null;
        $this->branch_type_id == 'all' ? $this->branch_type_id = null : null;

        $this->filters['order_status_id'] = $this->order_status_id;
        $this->filters['property_type_id'] = $this->property_type_id;
        $this->filters['city_id'] = $this->city_id;
        $this->filters['branch_type_id'] = $this->branch_type_id;
        $this->filters['search'] = $this->search;

        $user = auth()->user();

        if ($user->user_type == 'superadmin') {
            $collection = ModelsOrder::data()->filters($this->filters)->reorder($this->sort_field, $this->sort_direction);
            if ($this->rows_number == 'all') {
                $this->rows_number = $collection->count();
            }
        } else {
            $collection = ModelsOrder::data()->filters($this->filters)->reorder($this->sort_field, $this->sort_direction)->where('user_id', $user->id);
            if ($this->rows_number == 'all') {
                $this->rows_number = $collection->count();
            }
        }

        $data = $collection->paginate($this->rows_number);

        $this->paginate_ids = $data->pluck('id')->toArray();

        return $data;
    }

    public function render()
    {
        $orders = $this->getMainOrders();

        return view('livewire.order', [
            'orders' => $orders,
        ]);
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

    public function dateFrom()
    {
        $this->filters['date_from'] = $this->date_from;
    }

    public function dateTo()
    {
        $this->filters['date_to'] = $this->date_to;
    }

    public function callOrderModal($order_id)
    {
        $this->emit('openOrderModal', $order_id);
    }

    public function closeOrder($order_id)
    {
        $order = ModelsOrder::find($order_id);
        $user = auth()->user();

        if ($order) {
            if ($order->order_status_id == 3) {
                $order->update(['order_status_id' =>  5]);

                if ($user->user_type == 'admin' || $user->user_type == 'superadmin') {
                    $admin_name = getUserName($user->id);
                    $link_admin = route('panel.user', $user->id);
                    $admin = "<a href='$link_admin'> $admin_name</a>";
                    $note = "قام المدير $admin بتغير حالة الطلب";
                }

                OrderEditor::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'note' => $note,
                    'action' => 'active',
                ]);
            } else {
                $order->update(['order_status_id' => 3]);

                if ($user->user_type == 'admin' || $user->user_type == 'superadmin') {
                    $admin_name = getUserName($user->id);
                    $link_admin = route('panel.user', $user->id);
                    $admin = "<a href='$link_admin'> $admin_name</a>";
                    $note = "قام المدير $admin بإغلاق الطلب";
                }

                if ($user->user_type == 'office') {
                    $office_name = getUserName($user->id);
                    $link_office = route('panel.user', $user->id);
                    $office = "<a href='$link_office'> $office_name</a>";
                    $note = "قام المكتب $office بإغلاق الطلب";
                }

                OrderEditor::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'note' => $note,
                    'action' => 'cancel',
                ]);
            }
        }

        $this->alert('success', '', [
            'toast' => true,
            'position' => 'center',
            'timer' => 3000,
            'text' => '👍 تم تغيير حالة الطلب بنجاح',
            'timerProgressBar' => true,
        ]);
    }

    public function export($type)
    {
        if ($type == 'excel') {
            $excel = Excel::download(new OrdersExport($this->filters, $this->sort_field, $this->sort_direction, $this->rows_number, $this->paginate_ids, auth()->user()->user_type), 'orders.xlsx');

            $this->alert('success', '', [
                'toast' => true,
                'position' => 'center',
                'timer' => 6000,
                'text' => 'تم تصدير الملف بنجاح',
                'timerProgressBar' => true,
            ]);

            return $excel;
        }
    }
}
