<?php

namespace App\Models;

use Auth;
use Eloquent;
use Laracasts\Presenter\PresentableTrait;

/**
 * Class Timeline.
 */
class Timeline extends Eloquent
{
    use PresentableTrait;

    protected $table = 'core__timeline';

    /**
     * @var string
     */
    protected $presenter = 'App\Ninja\Presenters\TimelinePresenter';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeScope($query)
    {
        return $query->whereAccountId(Auth::user()->account_id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }

    /**
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    /**
     * @return mixed
     */
    public function contact()
    {
        return $this->belongsTo('App\Models\Contact')->withTrashed();
    }

    /**
     * @return mixed
     */
    public function client()
    {
        return $this->belongsTo('App\Models\Client')->withTrashed();
    }

    /**
     * @return mixed
     */
    public function invoice()
    {
        return $this->belongsTo('App\Models\Invoice')->withTrashed();
    }

    /**
     * @return mixed
     */
    public function credit()
    {
        return $this->belongsTo('App\Models\Credit')->withTrashed();
    }

    /**
     * @return mixed
     */
    public function payment()
    {
        return $this->belongsTo('App\Models\Payment')->withTrashed();
    }

    public function task()
    {
        return $this->belongsTo('App\Models\Task')->withTrashed();
    }

    public function expense()
    {
        return $this->belongsTo('App\Models\Expense')->withTrashed();
    }

    public function ticket()
    {
        return $this->belongsTo('App\Models\Ticket')->withTrashed();
    }


    public function key()
    {
        return sprintf('%s-%s-%s', $this->timeline_type_id, $this->client_id, $this->created_at->timestamp);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        $timelineTypeId = $this->timeline_type_id;
        $account = $this->account;
        $client = $this->client;
        $user = $this->user;
        $invoice = $this->invoice;
        $contactId = $this->contact_id;
        $payment = $this->payment;
        $credit = $this->credit;
        $expense = $this->expense;
        $isSystem = $this->is_system;
        $task = $this->task;
        $ticket = $this->ticket;

        $data = [
            'client' => $client ? link_to($client->getRoute(), $client->getDisplayName()) : null,
            'user' => $isSystem ? '<i>' . trans('texts.system') . '</i>' : e($user->getDisplayName()),
            'invoice' => $invoice ? link_to($invoice->getRoute(), $invoice->getDisplayName()) : null,
            'quote' => $invoice ? link_to($invoice->getRoute(), $invoice->getDisplayName()) : null,
            'contact' => $contactId ? link_to($client->getRoute(), $client->getDisplayName()) : e($user->getDisplayName()),
            'payment' => $payment ? e($payment->transaction_reference) : null,
            'payment_amount' => $payment ? $account->formatMoney($payment->amount, $payment) : null,
            'adjustment' => $this->adjustment ? $account->formatMoney($this->adjustment, $this) : null,
            'credit' => $credit ? $account->formatMoney($credit->amount, $client) : null,
            'task' => $task ? link_to($task->getRoute(), substr($task->description, 0, 30).'...') : null,
            'expense' => $expense ? link_to($expense->getRoute(), substr($expense->public_notes, 0, 30).'...') : null,
            'ticket' => $ticket ? link_to($ticket->getRoute(), $ticket->public_id) : null,

        ];

        return trans("texts.timeline_{$timelineTypeId}", $data);
    }

    public function relatedEntityType()
    {
        switch ($this->timeline_type_id) {
            case TIMELINE_TYPE_CREATE_CLIENT:
            case TIMELINE_TYPE_ARCHIVE_CLIENT:
            case TIMELINE_TYPE_DELETE_CLIENT:
            case TIMELINE_TYPE_RESTORE_CLIENT:
            case TIMELINE_TYPE_CREATE_CREDIT:
            case TIMELINE_TYPE_ARCHIVE_CREDIT:
            case TIMELINE_TYPE_DELETE_CREDIT:
            case TIMELINE_TYPE_RESTORE_CREDIT:
                return ENTITY_CLIENT;
                break;

            case TIMELINE_TYPE_CREATE_INVOICE:
            case TIMELINE_TYPE_UPDATE_INVOICE:
            case TIMELINE_TYPE_EMAIL_INVOICE:
            case TIMELINE_TYPE_VIEW_INVOICE:
            case TIMELINE_TYPE_ARCHIVE_INVOICE:
            case TIMELINE_TYPE_DELETE_INVOICE:
            case TIMELINE_TYPE_RESTORE_INVOICE:
                return ENTITY_INVOICE;
                break;

            case TIMELINE_TYPE_CREATE_PAYMENT:
            case TIMELINE_TYPE_ARCHIVE_PAYMENT:
            case TIMELINE_TYPE_DELETE_PAYMENT:
            case TIMELINE_TYPE_RESTORE_PAYMENT:
            case TIMELINE_TYPE_VOIDED_PAYMENT:
            case TIMELINE_TYPE_REFUNDED_PAYMENT:
            case TIMELINE_TYPE_FAILED_PAYMENT:
                return ENTITY_PAYMENT;
                break;

            case TIMELINE_TYPE_CREATE_QUOTE:
            case TIMELINE_TYPE_UPDATE_QUOTE:
            case TIMELINE_TYPE_EMAIL_QUOTE:
            case TIMELINE_TYPE_VIEW_QUOTE:
            case TIMELINE_TYPE_ARCHIVE_QUOTE:
            case TIMELINE_TYPE_DELETE_QUOTE:
            case TIMELINE_TYPE_RESTORE_QUOTE:
            case TIMELINE_TYPE_APPROVE_QUOTE:
                return ENTITY_QUOTE;
                break;

            case TIMELINE_TYPE_CREATE_VENDOR:
            case TIMELINE_TYPE_ARCHIVE_VENDOR:
            case TIMELINE_TYPE_DELETE_VENDOR:
            case TIMELINE_TYPE_RESTORE_VENDOR:
            case TIMELINE_TYPE_CREATE_EXPENSE:
            case TIMELINE_TYPE_ARCHIVE_EXPENSE:
            case TIMELINE_TYPE_DELETE_EXPENSE:
            case TIMELINE_TYPE_RESTORE_EXPENSE:
            case TIMELINE_TYPE_UPDATE_EXPENSE:
                return ENTITY_EXPENSE;
                break;

            case TIMELINE_TYPE_CREATE_TASK:
            case TIMELINE_TYPE_UPDATE_TASK:
            case TIMELINE_TYPE_ARCHIVE_TASK:
            case TIMELINE_TYPE_DELETE_TASK:
            case TIMELINE_TYPE_RESTORE_TASK:
                return ENTITY_TASK;
                break;
        }
    }
}
