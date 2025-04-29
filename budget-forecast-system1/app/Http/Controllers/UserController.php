<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with("roles", "costCenters")->paginate(15); // Eager load relationships
        return view("admin.users.index", compact("users"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $costCenters = CostCenter::all();
        return view("admin.users.create", compact("roles", "costCenters"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => ["required", "string", "max:255"],
            "email" => ["required", "string", "lowercase", "email", "max:255", "unique:".User::class],
            "password" => ["required", "confirmed", Rules\Password::defaults()],
            "roles" => ["required", "array"],
            "roles.*" => ["exists:roles,id"],
            "cost_centers" => ["sometimes", "array"],
            "cost_centers.*" => ["exists:cost_centers,id"],
        ]);

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);

        $user->roles()->sync($request->roles);
        if ($request->has("cost_centers")) {
            $user->costCenters()->sync($request->cost_centers);
        }

        return redirect()->route("users.index")->with("success", "Usuário criado com sucesso.");
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Usually not needed for admin management, redirect to edit or index
        return redirect()->route("users.edit", $user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $costCenters = CostCenter::all();
        $user->load("roles", "costCenters"); // Eager load relationships
        return view("admin.users.edit", compact("user", "roles", "costCenters"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            "name" => ["required", "string", "max:255"],
            "email" => ["required", "string", "lowercase", "email", "max:255", "unique:".User::class.",email,".$user->id],
            "password" => ["nullable", "confirmed", Rules\Password::defaults()],
            "roles" => ["required", "array"],
            "roles.*" => ["exists:roles,id"],
            "cost_centers" => ["sometimes", "array"],
            "cost_centers.*" => ["exists:cost_centers,id"],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled("password")) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $user->roles()->sync($request->roles);
        // Sync cost centers only if provided, otherwise detach all
        $user->costCenters()->sync($request->input("cost_centers", [])); 

        return redirect()->route("users.index")->with("success", "Usuário atualizado com sucesso.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting the currently logged-in user or a default admin if needed
        if ($user->id === auth()->id()) {
             return redirect()->route("users.index")->with("error", "Você não pode excluir a si mesmo.");
        }
        // Add more checks if necessary (e.g., prevent deleting the last admin)

        $user->delete();
        return redirect()->route("users.index")->with("success", "Usuário excluído com sucesso.");
    }
}
