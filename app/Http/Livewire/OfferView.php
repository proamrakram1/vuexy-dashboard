<?php

namespace App\Http\Livewire;

use App\Http\Controllers\Services\ReservationService;
use App\Models\Offer;
use App\Models\OfferEditors;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class OfferView extends Component
{
    use LivewireAlert;
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['updateOffer', 'refreshComponent' => '$refresh'];

    public $offer;
    public $real_estate;
    public $offer_id;
    public $last_update_time = '';
    public $last_update_note_time = '';

    public $customer_id;
    public $price;
    public $date;
    public $reservation_notes;

    public function mount($offer_id)
    {
        $this->offer_id = $offer_id;
        $this->offer = Offer::find($offer_id);
        $this->real_estate = $this->offer->realEstate;
    }

    public function render()
    {
        $this->getLastUpateTime();
        return view('livewire.offer-view');
    }

    public function hydrate()
    {
        $check = false;
        if ($this->offer->reservation) {
            $check = true;
        }
        $this->emit('select2', $check);
    }

    public function reservationData()
    {
        if ($this->offer->reservation) {
            $this->customer_id = $this->offer->reservation->customer->id;
            $this->price = $this->offer->reservation->price;
            $this->date = $this->offer->reservation->date_from . ' to ' . $this->offer->reservation->date_to;
            $this->reservation_notes = $this->offer->reservation->note;
        }
    }

    public function getLastUpateTime()
    {
        if ($this->offer) {
            if ($this->offer->updated_at) {
                $last_update = $this->offer->updated_at->toDateTimeString();
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

                $this->last_update_time = 'اخر تحديث منذ ' . $secs . ' ثانية';
                return true;
            }
        }
    }

    public function getLastUpateOfferEditTime($order_edit_id)
    {
        $order_edit_id = OfferEditors::find($order_edit_id);

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

    public function updated($propertyName, $value)
    {
        $this->validateOnly($propertyName);
    }

    public function getFields()
    {
        return ['customer_id', 'price', 'date', 'reservation_notes'];
    }

    public function rules()
    {
        $fields = $this->getFields();

        $validation = [];

        foreach ($fields as $field) {
            $validation[$field] = ['required'];
        }

        return $validation;
    }

    public function messages()
    {

        $fields = $this->getFields();

        $validation = [];

        foreach ($fields as $field) {
            $validation[$field . '.required'] = 'هذا الحقل مطلوب';
        }

        return $validation;
    }
    public function storeReservation(ReservationService $reservationService)
    {
        $data = $this->validate();
        $reservation = $reservationService->store($data, $this->offer);

        if ($reservation) {
            $this->alert('success', '', [
                'toast' => true,
                'position' => 'center',
                'timer' => 3000,
                'text' => '👍 تم حجز العرض بنجاح',
                'timerProgressBar' => true,
            ]);
        }

        $this->emit('refreshComponent');
        $this->emit('submitReservation');
    }
}
