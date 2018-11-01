<?php

namespace App\Ninja\Transformers;

use App\Models\Timeline;

/**
 * @SWG\Definition(definition="Timeline", @SWG\Xml(name="Timeline"))
 */
class TimelineTransformer extends EntityTransformer
{
	  /**
     * @SWG\Property(property="id", type="integer", example=1)
     * @SWG\Property(property="timeline_type_id", type="integer", example=1)
     * @SWG\Property(property="client_id", type="integer", example=1)
     * @SWG\Property(property="user_id", type="integer", example=1)
     * @SWG\Property(property="invoice_id", type="integer", example=1)
     * @SWG\Property(property="payment_id", type="integer", example=1)
     * @SWG\Property(property="credit_id", type="integer", example=1)
     * @SWG\Property(property="updated_at", type="integer", example=1451160233, readOnly=true)
     * @SWG\Property(property="expense_id", type="integer", example=1)
     * @SWG\Property(property="is_system", type="boolean", example=false)
     * @SWG\Property(property="contact_id", type="integer", example=1)
     * @SWG\Property(property="task_id", type="integer", example=1)
     */

    protected $defaultIncludes = [];

    /**
     * @var array
     */
    protected $availableIncludes = [];

    /**
     * @param Timeline $timeline
     *
     * @return array
     */
    public function transform(Timeline $timeline)
    {
        return [
            'id' => $timeline->key(),
            'timeline_type_id' => (int) $timeline->timeline_type_id,
            'client_id' => $timeline->client ? (int) $timeline->client->public_id : null,
            'user_id' => (int) $timeline->user->public_id + 1,
            'invoice_id' => $timeline->invoice ? (int) $timeline->invoice->public_id : null,
            'payment_id' => $timeline->payment ? (int) $timeline->payment->public_id : null,
            'credit_id' => $timeline->credit ? (int) $timeline->credit->public_id : null,
            'updated_at' => $this->getTimestamp($timeline->updated_at),
            'expense_id' => $timeline->expense_id ? (int) $timeline->expense->public_id : null,
            'is_system' => $timeline->is_system ? (bool) $timeline->is_system : null,
            'contact_id' => $timeline->contact_id ? (int) $timeline->contact->public_id : null,
            'task_id' => $timeline->task_id ? (int) $timeline->task->public_id : null,
			'notes' => $timeline->notes ?: '',
			'adjustment' => (float) $timeline->adjustment,
			'balance' => (float) $timeline->balance,

        ];
    }
}
