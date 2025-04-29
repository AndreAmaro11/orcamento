<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $costCenters = CostCenter::paginate(15);
        return view("admin.cost_centers.index", compact("costCenters"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("admin.cost_centers.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "code" => ["required", "string", "max:255", "unique:".CostCenter::class],
            "name" => ["required", "string", "max:255"],
            "description" => ["nullable", "string"],
        ]);

        CostCenter::create($request->all());

        return redirect()->route("cost-centers.index")->with("success", "Centro de custo criado com sucesso.");
    }

    /**
     * Display the specified resource.
     */
    public function show(CostCenter $costCenter)
    {
        // Redirect to edit view
        return redirect()->route("cost-centers.edit", $costCenter);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CostCenter $costCenter)
    {
        return view("admin.cost_centers.edit", compact("costCenter"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CostCenter $costCenter)
    {
        $request->validate([
            "code" => ["required", "string", "max:255", "unique:".CostCenter::class.",code,".$costCenter->id],
            "name" => ["required", "string", "max:255"],
            "description" => ["nullable", "string"],
        ]);

        $costCenter->update($request->all());

        return redirect()->route("cost-centers.index")->with("success", "Centro de custo atualizado com sucesso.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CostCenter $costCenter)
    {
        // Add check if cost center is in use before deleting?
        try {
            $costCenter->delete();
            return redirect()->route("cost-centers.index")->with("success", "Centro de custo excluído com sucesso.");
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle potential foreign key constraint violation if cost center is linked to entries/users
            return redirect()->route("cost-centers.index")->with("error", "Não foi possível excluir o centro de custo. Verifique se ele está associado a usuários ou lançamentos.");
        }
    }
}
