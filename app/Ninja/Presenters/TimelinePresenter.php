<?php

namespace App\Ninja\Presenters;

use Laracasts\Presenter\Presenter;
use Utils;

class TimelinePresenter extends Presenter
{
    public function createdAt()
    {
        return Utils::timestampToDateTimeString(strtotime($this->entity->created_at));
    }

    public function createdAtDate()
    {
        return Utils::dateToString($this->entity->created_at);
    }

    public function user()
    {
        if ($this->entity->is_system) {
            return '<i>' . trans('texts.system') . '</i>';
        } else {
            return $this->entity->user->getDisplayName();
        }
    }

    public function notes()
    {
        if ($this->entity->notes) {
            return trans('texts.notes_' . $this->entity->notes);
        } elseif (in_array($this->entity->timeline_type_id, [TIMELINE_TYPE_EMAIL_INVOICE, TIMELINE_TYPE_EMAIL_QUOTE])) {
            return trans('texts.initial_email');
        } else {
            return '';
        }
    }
}
