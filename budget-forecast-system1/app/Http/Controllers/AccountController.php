<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = Account::paginate(15);
        return view("admin.accounts.index", compact("accounts"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("admin.accounts.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "code" => ["required", "string", "max:255", "unique:".Account::class],
            "name" => ["required", "string", "max:255"],
            // Add validation for parent_id if hierarchy is implemented
        ]);

        Account::create($request->all());

        return redirect()->route("accounts.index")->with("success", "Conta criada com sucesso.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        // Redirect to edit view
        return redirect()->route("accounts.edit", $account);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        return view("admin.accounts.edit", compact("account"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        $request->validate([
            "code" => ["required", "string", "max:255", "unique:".Account::class.",code,".$account->id],
            "name" => ["required", "string", "max:255"],
            // Add validation for parent_id if hierarchy is implemented
        ]);

        $account->update($request->all());

        return redirect()->route("accounts.index")->with("success", "Conta atualizada com sucesso.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        // Add check if account is in use before deleting?
        try {
            $account->delete();
            return redirect()->route("accounts.index")->with("success", "Conta excluída com sucesso.");
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle potential foreign key constraint violation if account is linked to entries
            return redirect()->route("accounts.index")->with("error", "Não foi possível excluir a conta. Verifique se ela está associada a lançamentos de orçamento ou previsão.");
        }
    }
}
