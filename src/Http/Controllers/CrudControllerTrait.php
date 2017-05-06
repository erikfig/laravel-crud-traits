<?php

namespace ErikFig\Http\Controllers;

use Illuminate\Http\Request;

trait CrudControllerTrait
{
    /**
     * Display a listing of the resource.
     * ?limit=20
     * ?order=title,asc
     * ?field=value -> where
     * ?like=field,value
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->all()['limit'] ?? 20;

        $order = $request->all()['order'] ?? null;
        if ($order !== null) {
            $order = explode(',', $order);
        }
        $order[0] = $order[0] ?? 'id';
        $order[1] = $order[1] ?? 'asc';

        $where = $request->all()['where'] ?? [];

        $like = $request->all()['like'] ?? null;
        if ($like) {
            $like = explode(',', $like);
            if (!isset($like[1])) {
                $like[0] = 'title';
                $like[1] = $like[0];
            }
            $like[1] = '%' . $like[1] . '%';
        }

        $result = $this->model->orderBy($order[0], $order[1])
        ->where(function ($query) use ($like) {
            if ($like) {
                return $query->where($like[0], 'like', $like[1]);
            }
            return $query;
        })
        ->where($where)
        ->with($this->relationships())
        ->paginate($limit);

        return view($this->path.'.index', ['results'=>$result]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view($this->path.'.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = $this->model->create($request->all());
        return redirect($this->redirectPath)->withInput();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $result = $this->model->with($this->relationships())
          ->findOrFail($id);
        return view($this->path.'.view', ['result'=>$result]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $result = $this->model->with($this->relationships())
          ->findOrFail($id);
        return view($this->path.'.edit', ['result'=>$result]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $result = $this->model->findOrFail($id);
        $result->update($request->all());
        return redirect($this->redirectPath)->withInput();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = $this->model->findOrFail($id);
        $result->delete();
        return redirect($this->redirectPath);
    }

    protected function relationships()
    {
        if (isset($this->relationships)) {
            return $this->relationships;
        }
        return [];
    }
}
