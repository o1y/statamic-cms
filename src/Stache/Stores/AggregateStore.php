<?php

namespace Statamic\Stache\Stores;

use Closure;
use Statamic\Facades\Task;

abstract class AggregateStore extends Store
{
    protected $stores;
    protected $childStoreCreator;

    public function __construct()
    {
        $this->stores = collect();
    }

    public function store($key)
    {
        if (! $this->stores->has($key)) {
            $this->stores->put($key, $this->createChildStore($key));
        }

        return $this->stores->get($key);
    }

    protected function createChildStore($key)
    {
        $store = $this->childStoreCreator
            ? call_user_func($this->childStoreCreator)
            : app($this->childStore);

        return $store
            ->setChildKey($key)
            ->setParent($this);
    }

    public function setChildStoreCreator(Closure $callback)
    {
        $this->childStoreCreator = $callback;

        return $this;
    }

    public function stores()
    {
        return $this->stores;
    }

    public function childDirectory($child)
    {
        return $this->directory.$child->childKey();
    }

    public function getItems($keys)
    {
        return collect($keys)->map(function ($key) {
            return $this->getItem($key);
        });
    }

    public function getItem($key)
    {
        [$store, $id] = explode('::', $key, 2);

        return $this->store($store)->getItem($id);
    }

    public function clear()
    {
        $this->discoverStores()->each->clear();
    }

    public function warm()
    {
        Task::run(...$this->warmClosures());
    }

    protected function warmClosures()
    {
        return $this->stores()->map(function ($store) {
            return function () use ($store) {
                $store->warm();
            };
        })->values()->all();
    }

    public function paths()
    {
        return $this->discoverStores()->flatMap(function ($store) {
            return $store->paths();
        });
    }

    abstract public function discoverStores();
}
