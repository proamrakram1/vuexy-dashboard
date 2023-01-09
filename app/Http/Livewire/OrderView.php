<?php

namespace App\Http\Livewire;

use App\Events\SuspendedOrder as EventsSuspendedOrder;
use App\Models\Offer;
use App\Models\OfferEditors;
use App\Models\Order;
use App\Models\OrderEditor;
use App\Models\OrderNote;
use App\Models\User;
use App\Notifications\SuspendedOrder;
use Illuminate\Support\Facades\Notification;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class OrderView extends Component
{
    use LivewireAlert;
    protected $listeners = ['updateOrderNotesStatuses', 'refreshComponent' => '$refresh'];

    public $order;
    public $last_update_time = 'لم يتم التعديل على هذا الطلب بعد';
    public $last_update_note_time = 'لم يتم التعديل على هذا الطلب بعد';
    public $text = '';
    public $status_note = 1;
    public $order_id;
    public $offers = [];
    public $order_note_statuses;
    public $offer_id;

    public function updateOrderNotesStatuses()
    {
        $this->order_note_statuses = getOrderNoteStatuse();
    }

    public function mount($order_id)
    {
        $this->order_id = $order_id;
        $this->order = Order::find($this->order_id);
        $this->order_note_statuses = getOrderNoteStatuse();
        $this->offers = Offer::get(['id', 'offer_code']);
        $this->getLastUpateTime();
    }

    public function offerConnect()
    {
        $user = auth()->user();

        $order = Order::find($this->order_id);
        $offer = Offer::find($this->offer_id);

        $offer->update([
            'order_id' => $order->id,
            'order_code' => $order->order_code
        ]);

        $order->update([
            'offer_id' => $offer->id,
            'offer_code' => $offer->offer_code
        ]);


        if ($user->user_type == 'marketer') {
            $link_ma =  route('panel.user', $user->id);
            $marketer = "<a href='$link_ma'>$user->name</a>";
            $note = "قام المسوق $marketer بربط العرض";
        }

        if ($user->user_type == 'admin' || $user->user_type == 'superadmin') {
            $link_admin =  route('panel.user', $user->id);
            $admin = "<a href='$link_admin'>$user->name</a>";
            $note = "قام المدير $admin بربط العرض";
        }

        if ($user->user_type == 'office') {
            $link_office =  route('panel.user', $user->id);
            $office = "<a href='$link_office'>$user->name</a>";
            $note = "قام المكتب $office بربط العرض";
        }

        OrderEditor::create([
            'order_id' => $this->order->id,
            'user_id' => auth()->id(),
            'note' => $note,
            'action' => 'edit',
        ]);

        OfferEditors::create([
            'offer_id' => $offer->id,
            'user_id' => $user->id,
            'note' => $note,
            'action' => 'edit',
        ]);

        $order->update([
            'who_edit' => auth()->id(),
            'order_status_id' => 2
        ]);

        $this->alert('success', '', [
            'toast' => true,
            'position' => 'center',
            'timer' => 3000,
            'text' => '👍 تم ربط العرض بنجاح',
            'timerProgressBar' => true,
        ]);

        $this->emit('submitOfferCode');
        $this->emit('refreshComponent');
    }

    public function render()
    {
        return view('livewire.order-view', [
            'order' => $this->order,
        ]);
    }

    protected function rules()
    {
        return [
            #Form One
            'text' => ['required'],
        ];
    }

    protected function messages()
    {
        return [
            #Form One
            'text.required' => 'هذا الحقل مطلوب',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }


    public function addNote()
    {
        $user = auth()->user();
        $validatedData = $this->validate();

        OrderNote::create([
            'note' => $this->text,
            'status' => $this->status_note,
            'order_id' => $this->order->id,
            'user_id' => auth()->id(),
        ]);

        $this->alert('success', '', [
            'toast' => true,
            'position' => 'center',
            'timer' => 3000,
            'text' => '👍 تم إضافة الملاحظة بنجاح',
            'timerProgressBar' => true,
        ]);

        if (!in_array($this->status_note, [1, 2, 3, 4]) || $this->status_note == 1) {
            $this->order->update([
                'who_edit' => auth()->id(),
                'order_status_id' => 4
            ]);
        }

        if ($this->status_note == 3) {

            if ($user->user_type == 'marketer') {

                $link_admin =  route('panel.user', $user->id);
                $marketer = "<a href='$link_admin'>$user->name</a>";
                $note = "قام المسوق $marketer بإغلاق الطلب";
            }

            if ($user->user_type == 'admin' || $user->user_type == 'superadmin') {
                $link_admin =  route('panel.user', $user->id);
                $admin = "<a href='$link_admin'>$user->name</a>";
                $note = "قام المدير $admin بإغلاق الطلب";
            }

            if ($user->user_type == 'office') {
                $link_office =  route('panel.user', $user->id);
                $office = "<a href='$link_office'>$user->name</a>";
                $note = "قام المكتب $office بإغلاق الطلب";
            }

            $this->order->update([
                'closed_date' => now(),
                'who_cancel' => auth()->id(),
                'order_status_id' => 3
            ]);

            OrderEditor::create([
                'order_id' => $this->order->id,
                'user_id' => auth()->id(),
                'note' => $note,
                'action' => 'cancel',
            ]);

            $this->alert('warning', '', [
                'toast' => true,
                'position' => 'center',
                'timer' => 3000,
                'text' => '👍 تم إغلاق الطلب',
                'timerProgressBar' => true,
            ]);
        }

        if ($this->status_note == 4) {

            $this->order->update([
                'who_edit' => auth()->id(),
                'order_status_id' => 6
            ]);

            if ($user->user_type == 'marketer') {
                $link_ma =  route('panel.user', $user->id);
                $marketer = "<a href='$link_ma'>$user->name</a>";
                $note = "قام المسوق $marketer بتعليق الطلب";
            }

            if ($user->user_type == 'admin' || $user->user_type == 'superadmin') {
                $link_admin =  route('panel.user', $user->id);
                $admin = "<a href='$link_admin'>$user->name</a>";
                $note = "قام المدير $admin بتعليق الطلب";
            }

            if ($user->user_type == 'office') {
                $link_office =  route('panel.user', $user->id);
                $office = "<a href='$link_office'>$user->name</a>";
                $note = "قام المكتب $office بتعليق الطلب";
            }

            OrderEditor::create([
                'order_id' => $this->order->id,
                'user_id' => auth()->id(),
                'note' => $note,
                'action' => 'suspended',
            ]);

            $order = Order::find($this->order_id);

            if ($user->user_type == 'marketer') {
                $admins = User::whereIn('user_type', ['superadmin', 'admin'])->get();
                Notification::send($admins, new SuspendedOrder($order));
                event(new EventsSuspendedOrder($order));

                $this->alert('success', '', [
                    'toast' => true,
                    'position' => 'center',
                    'timer' => 6000,
                    'text' => '👍 تم إرسال إشعار بتعليق الطلب للإدارة بنجاح',
                    'timerProgressBar' => true,
                ]);
            } else {
                $this->alert('success', '', [
                    'toast' => true,
                    'position' => 'center',
                    'timer' => 6000,
                    'text' => '👍 تم تعليق الطلب بنجاح',
                    'timerProgressBar' => true,
                ]);
            }
        }

        $this->emit('submitNote');
        $this->emit('refreshComponent');
        $order_id = $this->order->id;
        $this->text = '';
        $this->order = Order::find($order_id);
    }



    public function getLastUpateTime()
    {
        if ($this->order) {
            if ($this->order->updated_at) {
                $last_update = $this->order->updated_at->toDateTimeString();
                $time_now = now();

                $datetime1 = strtotime($last_update);
                $datetime2 = strtotime($time_now);

                $secs = $datetime2 - $datetime1; // == <seconds between the two times>
                $min = $secs / 60;
                $hour = $secs / 3600;
                $days = $secs / 86400;


                if ($days > 0.99) {
                    $this->last_update_time = 'اخر تحديث منذ ' . round($days, 0) . ' يوم';
                    return true;
                }

                if ($hour > 0.99) {
                    $this->last_update_time = 'اخر تحديث منذ ' . round($hour, 0) . ' ساعة';
                    return true;
                }

                if ($min > 0.99) {
                    $this->last_update_time = 'اخر تحديث منذ ' . round($min, 0)  . ' دقيقة';
                    return true;
                }

                $this->last_update_time = 'اخر تحديث منذ ' . $secs . ' ثواني';
                return true;
            }
        }
    }

    public function getLastUpateOrderEditTime($order_edit_id)
    {
        $order_edit_id = OrderEditor::find($order_edit_id);

        $last_update = $order_edit_id->created_at->toDateTimeString();

        if ($last_update) {
            $time_now = now();

            $datetime1 = strtotime($last_update);
            $datetime2 = strtotime($time_now);

            $secs = $datetime2 - $datetime1;
            $min = $secs / 60;
            $hour = $secs / 3600;
            $days = $secs / 86400;


            if ($days > 0.99) {
                return 'منذ ' . round($days, 0) . ' يوم';
            }

            if ($hour > 0.99) {
                return 'منذ ' . round($hour, 0) . ' ساعة';
            }

            if ($min > 0.99) {
                return 'منذ ' . round($min, 0)  . ' دقيقة';
            }

            return 'منذ ' . $secs . ' ثواني';
        }
    }

    public function activateOrder()
    {
        $order = $this->order;
        $user = auth()->user();

        if ($order) {
            if ($order->order_status_id == 3) {
                $order->update(['order_status_id' =>  5]);

                if ($user->user_type == 'admin' || $user->user_type == 'superadmin') {
                    $link_admin =  route('panel.user', $user->id);
                    $admin = "<a href='$link_admin'>$user->name</a>";
                    $note = "قام المدير $admin بتنشيط الطلب";
                }

                if ($user->user_type == 'office') {
                    $link_office =  route('panel.user', $user->id);
                    $office = "<a href='$link_office'>$user->name</a>";
                    $note = "قام المكتب $office بتنشيط الطلب";
                }

                OrderEditor::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'note' => $note,
                    'action' => 'active',
                ]);
            }

            $this->alert('success', '', [
                'toast' => true,
                'position' => 'center',
                'timer' => 3000,
                'text' => '👍 تم تنشيط الطلب بنجاح',
                'timerProgressBar' => true,
            ]);
        }

        $this->emit('refreshComponent');
    }

    public function closeOrder()
    {
        $order = $this->order;
        $user = auth()->user();

        if ($order) {
            if ($order->order_status_id == 6) {
                $order->update(['order_status_id' =>  3]);

                if ($user->user_type == 'admin' || $user->user_type == 'superadmin') {

                    $link_admin =  route('panel.user', $user->id);
                    $admin = "<a href='$link_admin'>$user->name</a>";
                    $note = "قام المدير $admin بإغلاق الطلب";
                }

                if ($user->user_type == 'office') {
                    $link_office =  route('panel.user', $user->id);
                    $office = "<a href='$link_office'>$user->name</a>";
                    $note = "قام المكتب $office بإغلاق الطلب";
                }

                OrderEditor::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'note' => $note,
                    'action' => 'active',
                ]);
            }

            $this->alert('success', '', [
                'toast' => true,
                'position' => 'center',
                'timer' => 3000,
                'text' => '👍 تم إغلاق الطلب بنجاح',
                'timerProgressBar' => true,
            ]);
        }

        $this->emit('refreshComponent');
    }
}
