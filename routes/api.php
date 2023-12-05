<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/sueldo', function (Request $request) {
    /*$request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));

    Auth::login($user);

    return redirect(RouteServiceProvider::HOME);*/
    $request->validate([
        'dias_trabajados' => ['required', 'integer', 'min:1'],
        'salario' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
        'valor_ventas' => ['integer', 'min:0']
    ]);

    $comision = 0;
    if($request->valor_ventas <= 1000){
        $comision = (1/100)*$request->valor_ventas;
    }else if($request->valor_ventas > 1000 && $request->valor_ventas <= 5000){
        $comision = (5/100)*$request->valor_ventas;
    }else{
        $comision = (10/100)*$request->valor_ventas;
    }
    $salario_calculado = doubleval($request->salario);
    $porcentaje_prorrateo = 0;
    if($request->dias_trabajados<30){
        $dias_mes_actual = now()->daysInMonth;
        $porcentaje_prorrateo = (($dias_mes_actual - $request->dias_trabajados) / $dias_mes_actual);
        $salario_calculado -= $salario_calculado*$porcentaje_prorrateo;
        $comision -= $comision*$porcentaje_prorrateo; 
    }

    return response()->json([
        'salario_base' => $request->salario,
        'dias_trabajados' => $request->dias_trabajados,
        'valor_ventas' => $request->valor_ventas,
        'salario_calculado' => number_format(round($salario_calculado, 2), 2, '.', ''),
        'comisiones_ganadas' => number_format(round($comision, 2), 2, '.', ''),
        'porcentaje_prorrateo' => number_format(round(($porcentaje_prorrateo*100), 2), 2, '.', '').'%'
    ]);
});