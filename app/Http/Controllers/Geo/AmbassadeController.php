<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Models\Ambassade;
use App\Models\Pays;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AmbassadeController extends Controller
{
    public function index()
    {
        $ambassades = Ambassade::with('pays')->paginate(10);
        
        return Inertia::render('Dashboard/Geo/Ambassades/Index', [
            'ambassades' => $ambassades,
            'allPays' => Pays::select('code', 'nom')->orderBy('nom')->get(),
            'title' => 'Ambassades/Consulats'
        ]);
    }

    public function create()
    {
        $pays = Pays::all();
        
        return Inertia::render('Geo/Ambassades/Form', [
            'pays' => $pays,
            'title' => 'Créer une ambassade/consulat'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:5|unique:ambassades',
            'nom' => 'required|string|max:200',
            'type' => 'required|in:Ambassade,Consulat,Haut-Commissariat',
            'pays_code' => 'required|exists:pays,code',
            'ville' => 'required|string|max:100',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'nombre_electeurs' => 'required|integer|min:0'
        ]);

        Ambassade::create($validated);

        return redirect()->route('admin.geo.ambassades.index')
                         ->with('success', 'Ambassade/Consulat créé avec succès');
    }

    public function edit(Ambassade $ambassade)
    {
        $pays = Pays::all();
        
        return Inertia::render('Geo/Ambassades/Form', [
            'ambassade' => $ambassade,
            'pays' => $pays,
            'title' => 'Modifier l\'ambassade/consulat'
        ]);
    }

    public function update(Request $request, $ambassade)
    {
        $ambassade = Ambassade::where('code', $ambassade)->firstOrFail();
        $validated = $request->validate([
            'nom' => 'required|string|max:200',
            'type' => 'required|in:Ambassade,Consulat,Haut-Commissariat',
            'pays_code' => 'required|exists:pays,code',
            'ville' => 'required|string|max:100',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'nombre_electeurs' => 'required|integer|min:0'
        ]);

        $ambassade->update($validated);

        return redirect()->route('admin.geo.ambassades.index')
                         ->with('success', 'Ambassade/Consulat mis à jour');
    }

    public function destroy(Ambassade $ambassade)
    {
        $ambassade->delete();

        return redirect()->route('admin.geo.ambassades.index')
                         ->with('success', 'Ambassade/Consulat supprimé');
    }
}