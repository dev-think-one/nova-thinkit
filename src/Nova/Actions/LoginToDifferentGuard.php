<?php

namespace NovaThinKit\Nova\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Http\Requests\NovaRequest;
use ThinkStudio\HtmlField\Html;

class LoginToDifferentGuard extends DestructiveAction
{
    public $showInline            = true;
    public $showOnIndex           = false;
    public $showOnDetail          = true;
    public ?\Closure $findIdUsing = null;

    public function __construct(
        protected string $redirectPath,
        protected string $authGuard,
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
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $model = $models->first();
        if ($model
            && $model instanceof Model
            && ($modelId = $this->findId($model))
        ) {
            Auth::guard($this->authGuard)->logout();
            Auth::guard($this->authGuard)->loginUsingId($modelId, (bool) $fields->get('remember_me'));

            return Action::openInNewTab($this->redirectPath);
        }

        return Action::danger(trans('nova-thinkit::action.login-as.error.user-not-found'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Html::make(trans('nova-thinkit::action.login-as.warning.label'), function () {
                return $this->confirmText;
            }),
            Boolean::make(trans('nova-thinkit::action.login-as.remember.label'), 'remember_me')
                   ->help(trans('nova-thinkit::action.login-as.remember.help')),
        ];
    }

    protected function findId(Model $model)
    {
        if (is_callable($this->findIdUsing)) {
            return call_user_func_array($this->findIdUsing, [$model]);
        }

        return $model->getKey();
    }

    public function findIdUsing(?\Closure $findIdUsing): static
    {
        $this->findIdUsing = $findIdUsing;

        return $this;
    }
}
