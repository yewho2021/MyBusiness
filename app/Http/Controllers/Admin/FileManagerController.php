<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class FileManagerController extends Controller
{
    /**
     * Display the file manager page
     */
    public function index()
    {
        return view('admin.pages.filemanager.index');
    }
}