<?php

namespace Spatie\BinaryUuid;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Builder extends EloquentBuilder {
  /**
   * Find a model by its primary key.
   *
   * @param  mixed  $id
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
   */
  public function find($id, $columns = ['*'])
  {
      if (is_array($id) || $id instanceof Arrayable) {
          return $this->findMany($id, $columns);
      }

      return $this->withUuid($id)->first($columns);
  }

  /**
   * Find multiple models by their primary keys.
   *
   * @param  \Illuminate\Contracts\Support\Arrayable|array  $ids
   * @param  array  $columns
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function findMany($ids, $columns = ['*'])
  {
      if (empty($ids)) {
          return $this->model->newCollection();
      }

      return $this->withUuid($ids)->get($columns);
  }
}
