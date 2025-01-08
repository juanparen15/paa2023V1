<?php

namespace App\Http\Controllers;

use App\Planadquisicione;
use App\Requipoai;
use App\Area;
use App\Clase;
use App\Requiproyecto;
use App\Fuente;
use App\Mese;
use App\Modalidade;
use App\Estadovigencia;
use App\Exports\PlanadquisicioneAllExport;
use App\Exports\PlanadquisicioneExport;
use App\Familia;
use App\Intervalo;
use App\Producto;
use App\Segmento;
use App\Tipoadquisicione;
use App\Tipoprioridade;
use App\Tipozona;
use App\Tipoproceso;
use App\Vigenfutura;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PlanadquisicioneController extends Controller
{
    public function __construct()
    {

        $this->middleware([
            'auth',
            'permission:planadquisiciones.index',


        ]);
    }

    public function index(Request $request)
    {
        $years = Planadquisicione::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Cambiar la vigencia predeterminada a 2025
        //     $vigencia = $request->get('vigencia', date('Y'));

        $vigencia = $request->get('vigencia', '2025');

        if (auth()->user()->hasRole('Admin')) {
            $planadquisiciones = Planadquisicione::whereYear('created_at', $vigencia)->get();
        } else {
            $planadquisiciones = Planadquisicione::where('user_id', auth()->user()->id)
                ->whereYear('created_at', $vigencia)
                ->get();
        }

        return view('admin.planadquisiciones.index', compact('planadquisiciones', 'years', 'vigencia'));
    }

    public function create()
    {
        $productos = Producto::all();
        // $areas = Area::all();
        $userArea = auth()->user()->area;
        $areas = collect([$userArea]);
        $estadovigencias = Estadovigencia::get();
        $fuentes = Fuente::get();
        $meses = Mese::get();
        $modalidades = Modalidade::get();
        $requipoais = Requipoai::get();
        $requiproyectos = Requiproyecto::get();
        $tipoadquisiciones = Tipoadquisicione::get();
        $tipoprioridades = Tipoprioridade::get();
        $tipoprocesos = Tipoproceso::get();
        $tipozonas = Tipozona::get();
        $vigenfuturas = Vigenfutura::get();
        $intervalos = Intervalo::get();
        return view('admin.planadquisiciones.create', compact('intervalos', 'areas', 'estadovigencias', 'fuentes', 'meses', 'modalidades', 'requipoais', 'requiproyectos', 'tipoadquisiciones', 'tipoprioridades', 'tipoprocesos', 'tipozonas', 'vigenfuturas', 'productos'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'descripcioncont' => ['required'],
            'valorestimadocont' => ['required'],
            'valorestimadovig' => ['required'],
            'duracont' => ['required'],
            // 'codbpim' => ['required'],
            'area_id' => ['required'],
            'vigenfutura_id' => ['required'],
            'tipozona_id' => ['required'],
            'estadovigencia_id' => ['required'],
            'modalidade_id' => ['required'],
            // 'tipoproceso_id' => ['required'],
            'tipoadquisicione_id' => ['required'],
            'requiproyecto_id' => ['required'],
            'fuente_id' => ['required'],
            'tipoprioridade_id' => ['required'],
            'mese_id' => ['required'],
            'requipoai_id' => ['required'],
            'intervalo_id' => ['required'],
        ]);

        // Generar el slug base
        $slug = Str::slug($request->descripcioncont);

        // Verificar si ya existe un Planadquisicione con el mismo slug
        $counter = 1;
        while (Planadquisicione::where('slug', $slug)->exists()) {
            $slug = Str::slug($request->descripcioncont . '-' . $counter, '-');
            $counter++;
        }

        // Obtener el último ID en la tabla y agregar 1 para generar un número de orden único
        $ultimoId = Planadquisicione::max('id') + 1;
        $slugWithId = $slug . '-' . $ultimoId;

        // Crear el Planadquisicione con el slug único
        $planadquisicione = Planadquisicione::create($request->all() + [
            'user_id' => auth()->user()->id,
            'slug' => $slugWithId,
            // 'created_at' => '2025-01-01 00:00:00',
        ]);
        return redirect()->route('planadquisiciones.agregar_producto', $planadquisicione->slug)
            ->with('flash', 'Plan de adquisiciones registrado correctamente');
    }


    public function show(Request $request, Planadquisicione $planadquisicione)
    {
        $vigencia = $request->get('vigencia', date('Y'));

        return view('admin.planadquisiciones.show', compact('planadquisicione', 'vigencia'));
    }


    public function edit(Planadquisicione $planadquisicione)
    {
        $productos = Producto::all();
        $areas = Area::get();
        $estadovigencias = Estadovigencia::get();
        $fuentes = Fuente::get();
        $meses = Mese::get();
        $modalidades = Modalidade::get();
        $requipoais = Requipoai::get();
        $requiproyectos = Requiproyecto::get();
        $tipoadquisiciones = Tipoadquisicione::get();
        $tipoprioridades = Tipoprioridade::get();
        $tipoprocesos = Tipoproceso::get();
        $tipozonas = Tipozona::get();
        $vigenfuturas = Vigenfutura::get();
        $intervalos = Intervalo::get();
        return view('admin.planadquisiciones.edit', compact(
            'productos',
            'areas',
            'estadovigencias',
            'fuentes',
            'meses',
            'modalidades',
            'requipoais',
            'requiproyectos',
            'tipoadquisiciones',
            'tipoprioridades',
            'tipoprocesos',
            'tipozonas',
            'vigenfuturas',
            'planadquisicione',
            'intervalos'
        ));
    }

    public function update(Request $request, Planadquisicione $planadquisicione)
    {
        $request->validate([
            'descripcioncont' => ['required'],
            'valorestimadocont' => ['required'],
            'valorestimadovig' => ['required'],
            'duracont' => ['required'],
            // 'codbpim' => ['required'],
            'area_id' => ['required'],
            'vigenfutura_id' => ['required'],
            'tipozona_id' => ['required'],
            'estadovigencia_id' => ['required'],
            'modalidade_id' => ['required'],
            // 'tipoproceso_id' => ['required'],
            'tipoadquisicione_id' => ['required'],
            'requiproyecto_id' => ['required'],
            'fuente_id' => ['required'],
            'tipoprioridade_id' => ['required'],
            'mese_id' => ['required'],
            'requipoai_id' => ['required'],
            'intervalo_id' => ['required'],
        ]);

        // Crear el nuevo slug sin el -id
        $newSlugBase = Str::slug($request->descripcioncont);

        // Mantener el -id en el slug actual
        $existingId = last(explode('-', $planadquisicione->slug));
        $newSlug = $newSlugBase . '-' . $existingId;

        // Verificar si ya existe otro registro con el mismo slug pero diferente ID
        $counter = 1;
        while (Planadquisicione::where('slug', $newSlug)->where('id', '!=', $planadquisicione->id)->exists()) {
            $newSlug = $newSlugBase . '-' . $counter;
            $counter++;
        }

        // Actualizar el registro con el nuevo slug
        $planadquisicione->update($request->all() + [
            'user_id' => auth()->user()->id,
            'slug' => $newSlug,
        ]);

        return redirect()->route('planadquisiciones.index')->with('flash', 'actualizado');
    }

    // public function destroy(Planadquisicione $planadquisicion)
    // {
    //     $planadquisicion->delete();
        // return redirect()->route('planadquisiciones.index')->with('flash', 'eliminado');
    // }

    public function destroy(Planadquisicione $planadquisicione)
    {
        try {
            $planadquisicione->delete();
            return redirect()->route('planadquisiciones.index')->with('flash', 'eliminado');
        } catch (\Exception $e) {
            return redirect()->route('planadquisiciones.index')->with('error', 'No se pudo eliminar el registro: ' . $e->getMessage());
        }
    }


    public function retirar_producto(Planadquisicione $planadquisicione, Producto $producto)
    {

        // Intentar eliminar como producto
        if ($planadquisicione->productos()->where('productos.id', $producto->id)->exists()) {
            $planadquisicione->productos()->detach($producto->id);
            return redirect()->back()->with('flash', 'Producto eliminado correctamente.');
        }

        return redirect()->back()->withErrors('El código UNSPSC no está asociado con este plan.');
    }

    public function retirar_clase(Planadquisicione $planadquisicione, Clase $clase)
    {

        // Intentar eliminar como clase
        if ($planadquisicione->clases()->where('clases.id', $clase->id)->exists()) {
            $planadquisicione->clases()->detach($clase->id);
            return redirect()->back()->with('flash', 'Clase eliminada correctamente.');
        }

        return redirect()->back()->withErrors('El código UNSPSC no está asociado con este plan.');
    }

    public function exportar_planadquisiciones_excel(Planadquisicione $planadquisicion)
    {

        return Excel::download(new PlanadquisicioneExport($planadquisicion->id), 'plan_de_adquisicion_' . $planadquisicion->id . '.xlsx');
    }
    public function agregar_producto(Planadquisicione $planadquisicion)
    {
        $segmentos = Segmento::all();
        $clases = Clase::all();
        $familias = Familia::all(); // Nuevas familias añadidas
        $productos = Producto::all();

        return view('admin.planadquisiciones.agregar_producto', compact('planadquisicion', 'segmentos', 'clases', 'familias', 'productos'));
    }

    public function agregar_producto_store(Request $request, Planadquisicione $planadquisicion)
    {
        // Validar que al menos uno esté presente
        $request->validate([
            'producto_id' => 'nullable|exists:productos,id',
            'clase_id' => 'nullable|exists:clases,id',
        ]);

        if ($request->producto_id) {
            // Registrar el producto
            $planadquisicion->productos()->attach($request->producto_id);
        } elseif ($request->clase_id) {
            // Registrar la clase si no hay producto seleccionado
            $planadquisicion->clases()->attach($request->clase_id);
        } else {
            return redirect()->back()->withErrors('Debe seleccionar un producto o una clase.');
        }

        return redirect()->route('planadquisiciones.show', $planadquisicion)
            ->with('flash', 'Registro actualizado correctamente.');
    }

    public function export(Request $request)
    {
        // Obtenemos los años disponibles
        $years = Planadquisicione::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Obtenemos la vigencia seleccionada o usamos el año actual por defecto
        $vigencia = $request->get('vigencia', date('Y'));

        // Aplicamos el filtro de vigencia
        $query = Planadquisicione::whereYear('created_at', $vigencia);

        if (!auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Supervisor')) {
            // Filtramos por usuario si no es Admin ni Supervisor
            $query->where('user_id', auth()->user()->id);
        }

        // Cargamos las relaciones necesarias
        $planadquisiciones = $query->with(['productos', 'mese', 'modalidade', 'fuente', 'vigenfutura', 'estadovigencia'])->get();

        // Exportamos a Excel
        return Excel::download(new PlanadquisicioneAllExport($planadquisiciones), "plan_adquisiciones_{$vigencia}.xlsx");
    }
}
