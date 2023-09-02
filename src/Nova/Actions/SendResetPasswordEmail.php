<?php

namespace NovaThinKit\Nova\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Password;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use ThinkStudio\HtmlField\Html;

class SendResetPasswordEmail extends DestructiveAction
{
    use ForUser;

    public $showInline   = true;
    public $showOnIndex  = false;
    public $showOnDetail = true;

    public function __construct(
        protected string $broker,
        ?string          $name = null,
        ?string          $confirmText = null,
    ) {
        if ($name) {
            $this->name = $name;
        }
        if ($confirmText) {
            $this->confirmText = $confirmText;
        }
    }

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $model = $models->first();
        if (
            $model instanceof Model &&
            ($modelId = $this->findId($model))
        ) {
            $status = Password::broker($this->broker)->sendResetLink([
                $model->getKeyName() => $modelId,
            ]);

            if ($status !== Password::RESET_LINK_SENT) {
                return Action::danger(__($status));
            }

            return Action::message(__($status));
        }

        return Action::danger(trans('nova-thinkit::action.reset-password-notification.error.user-not-found'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Html::make(trans('nova-thinkit::action.reset-password-notification.warning.label'), function () {
                return $this->confirmText;
            }),
        ];
    }
}
