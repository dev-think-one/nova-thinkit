<?php

namespace NovaThinKit\Nova\Actions;

use Illuminate\Database\Eloquent\Model;

trait ForUser
{
    public ?\Closure $findIdUsing = null;

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
