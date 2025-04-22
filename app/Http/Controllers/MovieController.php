<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    /**
     * Common validation rules for movies
     * 
     * @param string|null $id Movie ID for update validation
     * @return array
     */
    private function getValidationRules($id = null)
    {
        // Base validation rules that apply to both create and update
        $rules = [
            'judul' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'sinopsis' => 'required|string',
            'tahun' => 'required|integer',
            'pemain' => 'required|string',
        ];

        // REFACTOR: Conditional validation for foto_sampul
        // - Required for new records
        // - Optional for updates
        $rules['foto_sampul'] = $id ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048' : 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        
        // REFACTOR: ID validation only needed for new records
        if (!$id) {
            $rules['id'] = ['required', 'string', 'max:255', Rule::unique('movies', 'id')];
        }
        
        return $rules;
    }

    /**
     * Handle file upload and storage
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @return string The filename of the stored file
     */
    private function handleFileUpload($file)
    {
        // REFACTOR: Centralized file handling logic
        // Generate a unique filename using UUID
        $randomName = Str::uuid()->toString();
        
        // REFACTOR: Now consistently using the actual file extension
        // instead of hardcoding to 'jpg' as in the original code
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = $randomName . '.' . $fileExtension;

        // Move the file to the public images directory
        $file->move(public_path('images'), $fileName);
        
        return $fileName;
    }

    /**
     * Display a listing of movies
     */
    public function index()
    {
        $query = Movie::latest();
        if (request('search')) {
            $query->where('judul', 'like', '%' . request('search') . '%')
                ->orWhere('sinopsis', 'like', '%' . request('search') . '%');
        }
        $movies = $query->paginate(6)->withQueryString();
        return view('homepage', compact('movies'));
    }

    /**
     * Display the specified movie
     */
    public function detail($id)
    {
        $movie = Movie::find($id);
        return view('detail', compact('movie'));
    }

    /**
     * Show the form for creating a new movie
     */
    public function create()
    {
        $categories = Category::all();
        return view('input', compact('categories'));
    }

    /**
     * Store a newly created movie
     */
    public function store(Request $request)
    {
        // REFACTOR: Using the centralized validation rules
        $validator = Validator::make($request->all(), $this->getValidationRules());
        
        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect('movies/create')
                ->withErrors($validator)
                ->withInput();
        }

        // REFACTOR: Using the centralized file upload handler
        // instead of duplicating file handling logic
        $fileName = $this->handleFileUpload($request->file('foto_sampul'));
        
        // Create the movie record
        Movie::create([
            'id' => $request->id,
            'judul' => $request->judul,
            'category_id' => $request->category_id,
            'sinopsis' => $request->sinopsis,
            'tahun' => $request->tahun,
            'pemain' => $request->pemain,
            'foto_sampul' => $fileName,
        ]);

        return redirect('/')->with('success', 'Data berhasil disimpan');
    }

    /**
     * Display a listing of movies for admin
     */
    public function data()
    {
        $movies = Movie::latest()->paginate(10);
        return view('data-movies', compact('movies'));
    }

    /**
     * Show the form for editing the specified movie
     */
    public function form_edit($id)
    {
        $movie = Movie::find($id);
        $categories = Category::all();
        return view('form-edit', compact('movie', 'categories'));
    }

    /**
     * Update the specified movie
     */
    public function update(Request $request, $id)
    {
        // REFACTOR: Using the centralized validation rules with ID parameter
        // to adjust validation for update scenario
        $validator = Validator::make($request->all(), $this->getValidationRules($id));
        
        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect("/movies/edit/{$id}")
                ->withErrors($validator)
                ->withInput();
        }

        // Get the movie to update
        $movie = Movie::findOrFail($id);
        
        // REFACTOR: Prepare data for update separately for better readability
        $updateData = [
            'judul' => $request->judul,
            'sinopsis' => $request->sinopsis,
            'category_id' => $request->category_id,
            'tahun' => $request->tahun,
            'pemain' => $request->pemain,
        ];

        // Handle file upload if a new file is provided
        if ($request->hasFile('foto_sampul')) {
            // REFACTOR: Using the centralized file upload handler
            $fileName = $this->handleFileUpload($request->file('foto_sampul'));
            
            // Delete the old file if it exists
            if (File::exists(public_path('images/' . $movie->foto_sampul))) {
                File::delete(public_path('images/' . $movie->foto_sampul));
            }
            
            // Add the new filename to the update data
            $updateData['foto_sampul'] = $fileName;
        }

        // REFACTOR: Single update call with all data
        // instead of conditional update logic
        $movie->update($updateData);

        return redirect('/movies/data')->with('success', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified movie
     */
    public function delete($id)
    {
        $movie = Movie::findOrFail($id);

        // Delete the movie's photo if it exists
        if (File::exists(public_path('images/' . $movie->foto_sampul))) {
            File::delete(public_path('images/' . $movie->foto_sampul));
        }

        // Delete the movie record from the database
        $movie->delete();

        return redirect('/movies/data')->with('success', 'Data berhasil dihapus');
    }
}
