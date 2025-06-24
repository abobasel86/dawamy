<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index()
    {
        $documentTypes = DocumentType::paginate(10);
        return view('admin.document_types.index', compact('documentTypes'));
    }

    public function create()
    {
        return view('admin.document_types.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:document_types,name']);
        DocumentType::create($request->all());
        return redirect()->route('admin.document-types.index')->with('success', 'تمت إضافة نوع المستند بنجاح.');
    }

    public function edit(DocumentType $documentType)
    {
        return view('admin.document_types.edit', compact('documentType'));
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $request->validate(['name' => 'required|string|max:255|unique:document_types,name,' . $documentType->id]);
        $documentType->update($request->all());
        return redirect()->route('admin.document-types.index')->with('success', 'تم تحديث نوع المستند بنجاح.');
    }

    public function destroy(DocumentType $documentType)
    {
        // يمكنك إضافة تحقق هنا للتأكد من عدم وجود مستندات مرتبطة بهذا النوع قبل حذفه
        $documentType->delete();
        return redirect()->route('admin.document-types.index')->with('success', 'تم حذف نوع المستند بنجاح.');
    }
}
