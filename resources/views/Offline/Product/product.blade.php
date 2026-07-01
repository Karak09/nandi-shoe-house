@extends('Offline.layouts.app')
@section('title', 'Professional Product Master - Shoe ERP')
@section('page_title', 'Product Catalog')
@section('content')
<style>
    .p-layout { height: calc(100vh - 140px); display: flex; flex-direction: column; }
    .p-layout .view-section { display: none; animation: fadeIn 0.25s ease; }
    .p-layout .view-section.active { display: flex; flex-direction: column; flex: 1; min-height: 0; }

    .p-layout .v-card { background: #fff; border-radius: 14px; border: 1px solid #e8ecf1; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; flex: 1; min-height: 0; }
    .p-layout .v-card-header { padding: 18px 24px; font-weight: 700; font-size: 15px; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e8ecf1; color: #0f172a; letter-spacing: -0.2px; flex-shrink: 0; }
    .p-layout .v-card-header .v-icon { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; margin-right: 10px; font-size: 14px; }
    .p-layout .v-card-header .v-icon.form-icon { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; }
    .p-layout .v-card-header .v-icon.table-icon { background: linear-gradient(135deg, #059669, #047857); color: #fff; }

    .p-layout .v-card-body { padding: 24px; flex: 1; overflow-y: auto; }
    .p-layout .v-card-body::-webkit-scrollbar { width: 5px; }
    .p-layout .v-card-body::-webkit-scrollbar-track { background: transparent; }
    .p-layout .v-card-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    .p-layout .v-fg { margin-bottom: 18px; }
    .p-layout .v-fg label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; letter-spacing: 0.2px; }
    .p-layout .v-fg label .required { color: #ef4444; }
    .p-layout .v-fg .v-input,
    .p-layout .v-fg .v-select { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.25s ease; background: #fafbfc; box-sizing: border-box; }
    .p-layout .v-fg .v-input:focus,
    .p-layout .v-fg .v-select:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
    .p-layout .v-fg .v-input:hover,
    .p-layout .v-fg .v-select:hover { border-color: #94a3b8; background: #fff; }
    .p-layout .v-fg textarea.v-input { min-height: 100px; resize: vertical; }

    .p-layout .v-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .p-layout .v-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }

    .p-layout .v-toggle-wrap { display: flex; align-items: center; gap: 14px; padding: 14px 18px; background: #f8fafc; border-radius: 10px; border: 1px solid #e8ecf1; }
    .p-layout .v-toggle { position: relative; width: 46px; height: 26px; flex-shrink: 0; }
    .p-layout .v-toggle input { opacity: 0; width: 0; height: 0; }
    .p-layout .v-toggle .v-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; transition: 0.3s; border-radius: 34px; }
    .p-layout .v-toggle .v-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; transition: 0.3s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
    .p-layout .v-toggle input:checked + .v-slider { background: linear-gradient(135deg, #10b981, #059669); }
    .p-layout .v-toggle input:checked + .v-slider:before { transform: translateX(20px); }
    .p-layout .v-toggle-label { font-size: 13px; font-weight: 600; color: #1e293b; }

    .p-layout .v-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; min-width: 750px; }
    .p-layout .v-table thead { position: sticky; top: 0; z-index: 2; }
    .p-layout .v-table thead tr { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
    .p-layout .v-table th { padding: 14px 18px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .p-layout .v-table td { padding: 14px 18px; font-size: 13px; border-bottom: 1px solid #f1f5f9; color: #0f172a; vertical-align: middle; transition: background 0.15s; }
    .p-layout .v-table tbody tr { transition: all 0.15s; }
    .p-layout .v-table tbody tr:hover td { background: #f8fafc; }

    .p-layout .product-cell { display: flex; align-items: center; gap: 12px; }
    .p-layout .img-thumb { width: 40px; height: 40px; border-radius: 6px; background: #e5e7eb; object-fit: cover; border: 1px solid #e2e8f0; background-size: cover; background-position: center; flex-shrink: 0; }
    .p-layout .prod-name { font-weight: 600; color: #0f172a; margin-bottom: 2px; }
    .p-layout .prod-sku { font-size: 11px; color: #64748b; }

    .p-layout .v-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.3px; }
    .p-layout .v-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
    .p-layout .v-badge.active { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .p-layout .v-badge.inactive { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

    .p-layout .v-action { background: none; border: none; cursor: pointer; padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; transition: all 0.2s; }
    .p-layout .v-action:hover { transform: translateY(-1px); }
    .p-layout .v-action-view { color: #059669; }
    .p-layout .v-action-view:hover { background: #ecfdf5; }
    .p-layout .v-action-edit { color: #2563eb; }
    .p-layout .v-action-edit:hover { background: #eff6ff; }
    .p-layout .v-action-delete { color: #ef4444; }
    .p-layout .v-action-delete:hover { background: #fef2f2; }

    .p-layout .v-table-wrap { flex: 1; overflow: auto; }

    .p-layout .upload-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 8px; }
    .p-layout .upload-wrapper { display: flex; flex-direction: column; }
    .p-layout .upload-box { position: relative; border: 2px dashed #d1d5db; border-radius: 8px; padding: 12px; text-align: center; cursor: pointer; transition: 0.2s; background: #f9fafb; background-size: cover; background-position: center; height: 90px; display: flex; flex-direction: column; align-items: center; justify-content: center; }

    .p-layout .upload-box.has-image { cursor: zoom-in; border-style: solid; border-color: #94a3b8; }
    .p-layout .upload-remove { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border-radius: 50%; background: rgba(239,68,68,0.85); color: #fff; border: none; font-size: 14px; line-height: 22px; text-align: center; cursor: pointer; display: none; z-index: 5; font-weight: bold; transition: 0.2s; padding: 0; }
    .p-layout .upload-remove:hover { background: #dc2626; transform: scale(1.15); }

    .p-layout .upload-icon { font-size: 18px; color: #9ca3af; margin-bottom: 4px; text-shadow: 0 0 4px rgba(255,255,255,0.8); }
    .p-layout .upload-text { font-size: 10px; font-weight: 600; color: #374151; background: rgba(255,255,255,0.8); padding: 2px 6px; border-radius: 4px; text-align: center; line-height: 1.3; }
    .p-layout .file-input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }

    .p-layout .modal-img { width: 100%; height: 120px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; cursor: zoom-in; transition: 0.2s; }
    .p-layout .modal-img:hover { opacity: 0.8; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }

    .p-layout .v-reset { padding: 6px 14px; background: #fff; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 12px; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; }
    .p-layout .v-reset:hover { background: #f1f5f9; border-color: #94a3b8; color: #0f172a; }
    .p-layout .v-submit { width: 100%; padding: 12px 24px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(37,99,235,0.25); letter-spacing: 0.2px; }
    .p-layout .v-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }
    .p-layout .colour-swatch { display: inline-block; width: 18px; height: 18px; border-radius: 4px; border: 1px solid #d1d5db; vertical-align: middle; margin-right: 6px; flex-shrink: 0; }
    .p-layout .colour-select-wrap { display: flex; align-items: center; gap: 8px; }
    .p-layout .colour-select-wrap select { flex: 1; }
    .p-layout .v-submit:active { transform: translateY(0); }
    .p-layout .v-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .p-layout .v-divider-label { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; padding-top: 4px; }
    .p-layout .v-divider-label .dash { flex: 1; height: 1px; background: linear-gradient(90deg, #e8ecf1, transparent); }

    @media (max-width: 992px) {
        .p-layout .v-grid-2 { grid-template-columns: 1fr !important; }
        .p-layout .v-grid-3 { grid-template-columns: 1fr !important; }
        .p-layout .upload-grid { grid-template-columns: repeat(2, 1fr) !important; }
        .p-layout .v-card-header { flex-wrap: wrap !important; gap: 12px !important; }
        #viewModal > div { width: 95% !important; max-width: 500px !important; }
        #viewModalContent > div { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 576px) {
        .p-layout .v-card-body { padding: 16px !important; }
        .p-layout .upload-grid { grid-template-columns: repeat(2, 1fr) !important; }
        .p-layout .v-table th,
        .p-layout .v-table td { padding: 10px 14px !important; font-size: 12px !important; }
        .p-layout .v-table { min-width: 600px; }
        #viewModalContent > div { gap: 8px !important; }
        #viewModal > div { padding: 16px !important; }
        #viewModalContent .img-grid { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>
<div class="p-layout">

    <div id="listView" class="view-section active">
        <section class="v-card">
            <div class="v-card-header">
                <span><span class="v-icon table-icon">📦</span> Products</span>
                <button class="v-submit" style="width:auto;padding:8px 18px;font-size:12px;" onclick="toggleView('addView', true)">+ Add New Product</button>
            </div>
            <div class="v-card-body" style="padding:0;">
                <div class="v-table-wrap">
                    <table id="prodTable" class="v-table datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Details</th>
                                <th>Category</th>
                                <th>Colour</th>
                                <th>Size & UOM</th>
                                <th>Codes (HSN/SKU)</th>
                                <th>Status</th>
                                <th data-sortable="false" style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $p)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="product-cell">
                                        <div class="img-thumb" style="background-image: url('{{ $p->images && $p->images->fst_image_doc ? asset('storage/'.$p->images->fst_image_doc) : 'https://via.placeholder.com/40' }}');"></div>
                                        <div>
                                            <div class="prod-name">{{ $p->name }}</div>
                                            <div class="prod-sku">Code: {{ $p->product_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
    @if($p->category)
        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#f1f5f9;border-radius:6px;font-size:12px;color:#1e293b;border:1px solid #e2e8f0;">
            @if($p->category->parent)
                <span style="color:#64748b;">{{ $p->category->parent->name }}</span>
                <span style="color:#94a3b8;">›</span>
            @endif
            <span style="font-weight:600;">{{ $p->category->name }}</span>
        </span>
    @else
        <span style="color:#94a3b8;">N/A</span>
    @endif
</td>
                                <td>
                                    @if($p->colourRelation)
                                        <span class="colour-swatch" style="background:{{ $p->colourRelation->colour_id }};width:14px;height:14px;border-radius:3px;border:1px solid #d1d5db;display:inline-block;vertical-align:middle;margin-right:4px;"></span>
                                        {{ $p->colourRelation->colour_name }}
                                    @else
                                        <span style="color:#94a3b8;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $p->pro_size ?: '-' }}</div>
                                    <div style="color:#6b7280; font-size:12px;">UOM: {{ $p->uomRelation ? $p->uomRelation->name : 'Unit' }}</div>
                                </td>
                                <td>
                                    <div>SKU: <span style="font-weight:600;">{{ $p->sku ?: '-' }}</span></div>
                                    <div style="color:#6b7280; font-size:12px;">HSN: {{ $p->hsn_code ?: '-' }}</div>
                                </td>
                                <td>
                                    @if($p->is_active)
                                        <span class="v-badge active"><span class="dot"></span>Active</span>
                                    @else
                                        <span class="v-badge inactive"><span class="dot"></span>Inactive</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <button class="v-action v-action-view" onclick='viewRecord(@json($p))' title="View Details">👁️ View</button>
                                    <button class="v-action v-action-edit" onclick='editRecord(@json($p))'>✏️ Edit</button>
                                    <button class="v-action v-action-delete" onclick="deleteRecord('{{ $p->encrypted_id }}')">🗑️ Delete</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div id="addView" class="view-section">
        <section class="v-card">
            <div class="v-card-header">
                <span><span class="v-icon form-icon">📋</span> <span id="formTitleText">Add New Product</span></span>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="v-reset" onclick="toggleView('listView')">Cancel</button>
                    <button type="button" id="btnSubmitTop" class="v-submit" style="width:auto;padding:8px 18px;font-size:12px;" onclick="document.getElementById('prodForm').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }))">Save Product</button>
                </div>
            </div>
            <div class="v-card-body">
                <form id="prodForm" novalidate>
                    <input type="hidden" id="encrypted_id" name="encrypted_id">

                    <div class="v-grid-2">
                        <div>
                            <div style="background:#fff;border-radius:10px;border:1px solid #e8ecf1;padding:20px;margin-bottom:20px;">
                                <div class="v-divider-label"><span>📝 General Information</span><span class="dash"></span></div>
                                <div class="v-grid-2">
                                    <div class="v-fg">
                                        <label>Product Name (English) <span class="required">*</span></label>
                                        <input type="text" id="name" name="name" class="v-input" placeholder="e.g. Nike Air Max 270" required>
                                    </div>
                                    <div class="v-fg">
                                        <label>Bengali Name</label>
                                        <input type="text" id="ben_name" name="ben_name" class="v-input" placeholder="e.g. নাইক এয়ার ম্যাক্স">
                                    </div>
                                </div>
                                <div class="v-fg">
                                    <label>Product Description</label>
                                    <textarea id="product_des" name="product_des" class="v-input" placeholder="Write a detailed description..."></textarea>
                                </div>
                            </div>

                            <div style="background:#fff;border-radius:10px;border:1px solid #e8ecf1;padding:20px;margin-bottom:20px;">
                                <div class="v-divider-label"><span>📸 Product Media</span><span class="dash"></span></div>
                                <div class="upload-grid" id="dynamicImageContainer">
                                    @php $slots = ['fst', 'sec', 'trd', 'foth', 'fiv', 'six', 'sev', 'eig']; @endphp
                                    @foreach($slots as $index => $s)
                                        <div class="upload-wrapper" id="wrapper_{{ $s }}" style="display: {{ $index === 0 ? 'flex' : 'none' }};">
                                            <div class="upload-box" id="box_{{ $s }}">
                                                <input type="file" id="file_{{ $s }}" class="file-input" accept="image/jpeg, image/png, image/jpg">
                                                <input type="hidden" id="{{ $s }}_image_base64" name="{{ $s }}_image_base64">
                                                <input type="hidden" id="{{ $s }}_image_name" name="{{ $s }}_image_name">
                                                <button type="button" class="upload-remove" id="remove_{{ $s }}" onclick="event.stopPropagation();removeUploadImage('{{ $s }}')" title="Remove image">&times;</button>
                                                <div class="upload-icon">📷</div>
                                                <div class="upload-text">Photo {{ $index + 1 }}<br><span style="color:#ef4444;">Max 70KB</span></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div>
                            <div style="background:#fff;border-radius:10px;border:1px solid #e8ecf1;padding:20px;margin-bottom:20px;">
                                <div class="v-divider-label"><span>🏷️ Organization & Attributes</span><span class="dash"></span></div>
                                <div class="v-fg">
                                    <label>Category <span class="required">*</span></label>
                                    <select id="cat_id" name="cat_id" class="v-select" required>
                                        <option value="">-- Select Category --</option>
                                        @php
                                            $catParentIds = $categories->pluck('parent_id')->filter()->unique()->toArray();
                                            $topLevelCats = $categories->whereNull('parent_id')->sortBy('name');
                                            $subCatsWithChildren = $categories->whereIn('id', $catParentIds)->whereNotNull('parent_id')->sortBy('name');
                                            $childrenByParentId = $categories->whereNotNull('parent_id')->groupBy('parent_id');
                                            $renderedIds = [];
                                        @endphp

                                        @foreach($topLevelCats as $cat)
                                            @php $renderedIds[] = $cat->id; @endphp
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @if(in_array($cat->id, $catParentIds) && $childrenByParentId->has($cat->id))
                                                @foreach($childrenByParentId->get($cat->id)->sortBy('name') as $child)
                                                    @php $renderedIds[] = $child->id; @endphp
                                                    @if(in_array($child->id, $catParentIds))
                                                        <option value="{{ $child->id }}">&nbsp;&nbsp;{{ $child->name }}({{ $cat->name }})</option>
                                                        @if($childrenByParentId->has($child->id))
                                                            @foreach($childrenByParentId->get($child->id)->sortBy('name') as $grandchild)
                                                                @php $renderedIds[] = $grandchild->id; @endphp
                                                                <option value="{{ $grandchild->id }}">&nbsp;&nbsp;&nbsp;&nbsp;{{ $grandchild->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    @else
                                                        <option value="{{ $child->id }}">&nbsp;&nbsp;{{ $child->name }}({{ $cat->name }})</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach

                                        @foreach($subCatsWithChildren as $sc)
                                            @if(!in_array($sc->id, $renderedIds))
                                                @php $renderedIds[] = $sc->id; @endphp
                                                @php $parentName = $sc->parent ? $sc->parent->name : ''; @endphp
                                                <option value="{{ $sc->id }}">&nbsp;&nbsp;{{ $sc->name }}({{ $parentName }})</option>
                                                @if($childrenByParentId->has($sc->id))
                                                    @foreach($childrenByParentId->get($sc->id)->sortBy('name') as $gc)
                                                        @php $renderedIds[] = $gc->id; @endphp
                                                        <option value="{{ $gc->id }}">&nbsp;&nbsp;&nbsp;&nbsp;{{ $gc->name }}</option>
                                                    @endforeach
                                                @endif
                                            @endif
                                        @endforeach

                                        @foreach($categories as $cat)
                                            @if(!in_array($cat->id, $renderedIds))
                                                @php $renderedIds[] = $cat->id; @endphp
                                                <option value="{{ $cat->id }}">{{ $cat->parent ? '&nbsp;&nbsp;' . $cat->name . '(' . $cat->parent->name . ')' : $cat->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="v-grid-2">
                                    <div class="v-fg">
                                        <label>Size (pro_size) <span class="required">*</span></label>
                                        <input type="number" id="pro_size" name="pro_size" class="v-input" placeholder="e.g. 8, 9" min="0" step="any" oninput="this.value = Math.abs(this.value)" required>
                                    </div>
                                    <div class="v-fg">
                                        <label>Unit (UOM) <span class="required">*</span></label>
                                        <select id="uom" name="uom" class="v-select" required>
                                            <option value="">Select Unit...</option>
                                            @foreach($units as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->keyword }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="v-toggle-wrap">
                                    <label class="v-toggle">
                                        <input type="checkbox" id="is_packet" name="is_packet">
                                        <span class="v-slider"></span>
                                    </label>
                                    <span class="v-toggle-label">Is Packet Item?</span>
                                </div>
                                <div class="v-fg">
                                <br>
                                    <label>Colour<span class="required">*</span></label>
                                    <div class="colour-select-wrap">
                                        <select id="colour_id" name="colour_id" class="v-select">
                                            <option value="">-- Select Colour --</option>
                                            @foreach($colours as $c)
                                                <option value="{{ $c->id }}" data-hex="{{ $c->colour_id }}">{{ $c->colour_name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="colour-swatch" id="colourSwatch" style="background:#ccc;"></span>
                                    </div>
                                </div>
                            </div>

                            <div style="background:#fff;border-radius:10px;border:1px solid #e8ecf1;padding:20px;margin-bottom:20px;">
                                <div class="v-divider-label"><span>🔑 Inventory Identifiers</span><span class="dash"></span></div>
                                <div class="v-fg">
                                    <label>Product Code <span class="required">*</span></label>
                                    <input type="text" id="product_code" name="product_code" class="v-input" placeholder="Unique system code" required>
                                </div>
                                <div class="v-fg">
                                    <label>SKU</label>
                                    <input type="text" id="sku" name="sku" class="v-input" placeholder="e.g. NK270-BLK-9">
                                </div>
                                <div class="v-fg">
                                    <label>HSN Code (Taxation)</label>
                                    <input type="text" id="hsn_code" name="hsn_code" class="v-input" placeholder="e.g. 64041190">
                                </div>
                                <hr style="border:0;border-top:1px solid #e8ecf1;margin:18px 0;">
                                <div class="v-toggle-wrap">
                                    <label class="v-toggle">
                                        <input type="checkbox" id="is_active" name="is_active" checked>
                                        <span class="v-slider"></span>
                                    </label>
                                    <span class="v-toggle-label" style="color:#059669;">Product is Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(4px);">
    <div onclick="event.stopPropagation()" style="background:#fff;padding:24px 32px;border-radius:16px;width:650px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);animation:modalIn 0.25s ease;">
        <div style="flex-shrink:0;">
            <h2 style="font-size:18px;font-weight:700;border-bottom:1px solid #e8ecf1;padding-bottom:14px;margin-bottom:16px;color:#0f172a;display:flex;align-items:center;gap:10px;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border-radius:8px;font-size:14px;">📦</span>
                Product Details
            </h2>
        </div>
        <div id="viewModalContent" style="font-size:14px;line-height:1.8;color:#334155;overflow-y:auto;padding-right:8px;flex-grow:1;"></div>
        <div style="flex-shrink:0;margin-top:24px;display:flex;justify-content:flex-end;">
            <button type="button" onclick="document.getElementById('viewModal').style.display='none'" class="v-reset" style="padding:8px 24px;">Close</button>
        </div>
    </div>
</div>

<div id="imageLightbox" onclick="if(event.target===this)closeLightbox()" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.95);z-index:10000;align-items:center;justify-content:center;">
    <button onclick="closeLightbox()" style="position:absolute;top:20px;right:40px;background:none;border:none;color:white;font-size:40px;cursor:pointer;font-weight:bold;z-index:10001;">&times;</button>
    <img id="lightboxImg" src="" style="transition:transform 0.3s ease;transform-origin:center center;max-width:90vw;max-height:90vh;cursor:grab;">
    <div style="position:absolute;bottom:40px;display:flex;gap:15px;background:rgba(255,255,255,0.15);padding:10px 20px;border-radius:30px;z-index:10001;">
        <button onclick="zoomImg(0.5)" style="padding:8px 16px;background:rgba(0,0,0,0.6);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:6px;cursor:pointer;font-weight:600;">➕ Zoom In</button>
        <button onclick="zoomImg(-0.5)" style="padding:8px 16px;background:rgba(0,0,0,0.6);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:6px;cursor:pointer;font-weight:600;">➖ Zoom Out</button>
    </div>
</div>

@push('scripts')
<script>
    // --- FULL IMAGE ZOOM LOGIC ---
    let currentZoom = 1;
    window.openLightbox = function(src) {
        currentZoom = 1;
        const img = document.getElementById('lightboxImg');
        img.style.transform = `scale(1)`;
        img.src = src;
        document.getElementById('imageLightbox').style.display = 'flex';
    };
    window.closeLightbox = function() {
        document.getElementById('imageLightbox').style.display = 'none';
    };
    window.zoomImg = function(step) {
        currentZoom += step;
        if(currentZoom < 0.5) currentZoom = 0.5;
        if(currentZoom > 5) currentZoom = 5;
        document.getElementById('lightboxImg').style.transform = `scale(${currentZoom})`;
    };

    // --- SIMPLE SEQUENTIAL IMAGE UPLOADER ---
    const imgSlots = ['fst', 'sec', 'trd', 'foth', 'fiv', 'six', 'sev', 'eig'];

    function markUploadState(slot, hasImage) {
        const box = document.getElementById(`box_${slot}`);
        const fi = document.getElementById(`file_${slot}`);
        const rm = document.getElementById(`remove_${slot}`);
        box.classList.toggle('has-image', hasImage);
        if (hasImage) {
            fi.style.pointerEvents = 'none';
            rm.style.display = 'block';
        } else {
            fi.style.pointerEvents = '';
            rm.style.display = 'none';
        }
    }

    imgSlots.forEach((slot, index) => {
        // Click on box with image → open lightbox
        document.getElementById(`box_${slot}`).addEventListener('click', function(e) {
            if (e.target.closest('.upload-remove, .file-input')) return;
            if (this.classList.contains('has-image')) {
                const bg = this.style.backgroundImage;
                if (bg && bg.startsWith('url(')) {
                    const src = bg.slice(5, -2).replace(/^['"]|['"]$/g, '');
                    if (src) openLightbox(src);
                }
            }
        });

        document.getElementById(`file_${slot}`).addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            document.getElementById(`${slot}_image_name`).value = file.name;
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById(`${slot}_image_base64`).value = e.target.result;
                document.getElementById(`box_${slot}`).style.backgroundImage = `url(${e.target.result})`;
                markUploadState(slot, true);

                if (index + 1 < imgSlots.length) {
                    const nextSlot = imgSlots[index + 1];
                    document.getElementById(`wrapper_${nextSlot}`).style.display = 'flex';
                } else {
                    toastr.warning('You can only upload 8 photos', 'Limit Reached', {
                        timeOut: 3000, closeButton: true, progressBar: true, positionClass: "toast-top-right"
                    });
                }
            };
            reader.readAsDataURL(file);
        });
    });

    window.removeUploadImage = function(slot) {
        const idx = imgSlots.indexOf(slot);
        const box = document.getElementById(`box_${slot}`);
        box.style.backgroundImage = 'none';
        document.getElementById(`file_${slot}`).value = '';
        document.getElementById(`${slot}_image_base64`).value = '';
        document.getElementById(`${slot}_image_name`).value = '';
        markUploadState(slot, false);

        // hide this wrapper and all after it; show the previous slot's next wrapper
        let lastFilled = -1;
        for (let i = 0; i < imgSlots.length; i++) {
            const s = imgSlots[i];
            const b = document.getElementById(`box_${s}`);
            const w = document.getElementById(`wrapper_${s}`);
            if (b.style.backgroundImage && b.style.backgroundImage !== 'none' && b.style.backgroundImage !== '') {
                lastFilled = i;
                w.style.display = 'flex';
            } else if (i > lastFilled) {
                w.style.display = 'none';
            }
        }
        // ensure at least one empty wrapper after the last filled
        if (lastFilled + 1 < imgSlots.length) {
            document.getElementById(`wrapper_${imgSlots[lastFilled + 1]}`).style.display = 'flex';
        } else if (lastFilled === -1) {
            document.getElementById(`wrapper_${imgSlots[0]}`).style.display = 'flex';
        }
    };

    function resetImageBoxes() {
        imgSlots.forEach((slot, index) => {
            document.getElementById(`wrapper_${slot}`).style.display = (index === 0) ? 'flex' : 'none';
            document.getElementById(`box_${slot}`).style.backgroundImage = 'none';
            document.getElementById(`file_${slot}`).value = '';
            document.getElementById(`${slot}_image_base64`).value = '';
            document.getElementById(`${slot}_image_name`).value = '';
            markUploadState(slot, false);
        });
    }

    function toggleView(viewId, isReset = false) {
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.getElementById(viewId).classList.add('active');
        if(isReset) {
            document.getElementById('prodForm').reset();
            document.getElementById('encrypted_id').value = '';
            document.getElementById('formTitleText').innerText = 'Add New Product';
            document.getElementById('btnSubmitTop').innerText = 'Save Product';
            window.clearFormErrors();
            resetImageBoxes();
        }
    }

    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.v-input, .v-select, input, select, textarea').forEach(el => el.style.borderColor = '');
    };

    // --- FORM SUBMIT ---
    document.getElementById('prodForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        window.clearFormErrors();

        const btn = document.getElementById('btnSubmitTop');
        btn.disabled = true;
        btn.innerHTML = 'Processing...';

        const encId = document.getElementById('encrypted_id').value;
        const method = encId ? 'PUT' : 'POST';
        const url = encId ? `/api/products/${encId}` : `/api/products`;

        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());
        payload.is_active = document.getElementById('is_active').checked ? 1 : 0;
        payload.is_packet = document.getElementById('is_packet').checked ? 1 : 0;

        try {
            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });

            if (!res.ok) throw await res.json();

            toastr.success((await res.json()).message);
            setTimeout(() => location.reload(), 1000);

        } catch (error) {
            btn.disabled = false;
            btn.innerHTML = encId ? 'Update Product' : 'Save Product';

            toastr.error(error.message || 'Please fix the validation errors.');

            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];

                    if (fieldName.includes('_image_base64')) {
                        let slot = fieldName.replace('_image_base64', '');
                        let wrapper = document.getElementById(`wrapper_${slot}`);
                        if (wrapper) {
                            wrapper.querySelectorAll('.custom-error-text').forEach(el => el.remove());
                            wrapper.insertAdjacentHTML('beforeend',
                                `<div class="custom-error-text" style="color:#ef4444;font-size:11px;margin-top:4px;font-weight:600;text-align:center;">${msg}</div>`
                            );
                            let box = document.getElementById(`box_${slot}`);
                            if (box) box.style.borderColor = '#ef4444';
                        }
                        continue;
                    }

                    let field = document.querySelector(`[name="${fieldName}"]`);
                    if (field && field.type !== 'hidden') {
                        field.style.borderColor = '#ef4444';
                        field.insertAdjacentHTML('afterend',
                            `<div class="custom-error-text" style="color:#ef4444;font-size:11px;margin-top:4px;font-weight:600;">${msg}</div>`
                        );
                    }
                }
            }
        }
    });

    // --- COLOUR SWATCH PREVIEW ---
    document.getElementById('colour_id').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        document.getElementById('colourSwatch').style.background = opt && opt.dataset.hex ? opt.dataset.hex : '#ccc';
    });

    // --- EDIT RECORD ---
    window.editRecord = function(record) {
        toggleView('addView');
        window.clearFormErrors();
        document.getElementById('formTitleText').innerText = 'Edit Product';
        document.getElementById('btnSubmitTop').innerText = 'Update Product';

        document.getElementById('encrypted_id').value = record.encrypted_id;
        document.getElementById('name').value = record.name;
        document.getElementById('ben_name').value = record.ben_name || '';
        document.getElementById('product_des').value = record.product_des || '';
        document.getElementById('product_code').value = record.product_code;
        document.getElementById('sku').value = record.sku || '';
        document.getElementById('hsn_code').value = record.hsn_code || '';
        document.getElementById('pro_size').value = record.pro_size || '';
        document.getElementById('cat_id').value = record.cat_id || '';
        document.getElementById('uom').value = record.uom || '';
        document.getElementById('colour_id').value = record.colour_id || '';
        const co = document.getElementById('colour_id');
        const cop = co.options[co.selectedIndex];
        document.getElementById('colourSwatch').style.background = cop && cop.dataset.hex ? cop.dataset.hex : '#ccc';

        document.getElementById('is_active').checked = record.is_active == 1;
        document.getElementById('is_packet').checked = record.is_packet == 1;

        resetImageBoxes();
        let lastFilledIndex = -1;

        if(record.images) {
            imgSlots.forEach((slot, index) => {
                let doc = record.images[`${slot}_image_doc`];
                if(doc) {
                    document.getElementById(`wrapper_${slot}`).style.display = 'flex';
                    document.getElementById(`box_${slot}`).style.backgroundImage = `url('/storage/${doc}')`;
                    markUploadState(slot, true);
                    lastFilledIndex = index;
                }
            });
        }

        if (lastFilledIndex + 1 < imgSlots.length) {
            document.getElementById(`wrapper_${imgSlots[lastFilledIndex + 1]}`).style.display = 'flex';
        }
    };

    // --- DELETE RECORD ---
    window.deleteRecord = async function(encId) {
        if(!confirm('Are you sure you want to soft delete this product?')) return;
        try {
            const res = await fetch(`/api/products/${encId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            if(!res.ok) throw await res.json();
            toastr.success((await res.json()).message);
            setTimeout(() => location.reload(), 1000);
        } catch (error) { toastr.error('Deletion failed.'); }
    };

    // --- VIEW MODAL ---
    window.viewRecord = function(record) {
        const val = (item) => item ? item : '-';

        let imagesHtml = '';
        if(record.images) {
            imgSlots.forEach(slot => {
                let doc = record.images[`${slot}_image_doc`];
                if(doc) imagesHtml += `<img src="/storage/${doc}" style="width:100%;height:110px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0;cursor:zoom-in;transition:0.2s;" title="Click to Zoom" onclick="openLightbox(this.src)">`;
            });
        }
        if(!imagesHtml) imagesHtml = '<p style="color:#64748b;font-style:italic;grid-column:1/-1;font-size:13px;">No images uploaded.</p>';

        const uomName = (record.uom_relation && record.uom_relation.name) ? record.uom_relation.name : (record.uom_name || 'Unit');
        const catName = record.category ? (record.category.parent ? record.category.parent.name + ' > ' : '') + record.category.name : '-';
        const colourName = (record.colour_relation && record.colour_relation.colour_name) ? record.colour_relation.colour_name : (record.colour_name || null);
        const colourHex = (record.colour_relation && record.colour_relation.colour_id) ? record.colour_relation.colour_id : (record.colour_hex || null);

        let imgCount = 0;
        if(record.images) {
            imgSlots.forEach(slot => { if(record.images[`${slot}_image_doc`]) imgCount++; });
        }
        const imgCols = imgCount > 2 ? 'repeat(4,1fr)' : 'repeat(' + Math.min(imgCount || 1, 4) + ',1fr)';
        const content = `
            <div class="img-grid" style="display:grid;grid-template-columns:${imgCols};gap:10px;margin-bottom:18px;background:#f8fafc;padding:14px;border-radius:10px;border:1px solid #e8ecf1;">
                <div style="grid-column:1/-1;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">📸 Product Media (Click to Zoom)</div>
                ${imagesHtml}
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Product Name</strong><br>
                    <span style="color:#0f172a;font-weight:700;font-size:16px;">${val(record.name)}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Bengali Name</strong><br>
                    <span style="color:#0f172a;font-size:15px;">${val(record.ben_name)}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Category</strong><br>
                    <span style="color:#0f172a;">${catName}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Status</strong><br>
                    ${record.is_active
                        ? '<span style="display:inline-flex;align-items:center;gap:6px;color:#059669;font-weight:700;background:#ecfdf5;padding:4px 12px;border-radius:6px;"><span style="width:8px;height:8px;border-radius:50%;background:#10b981;"></span>Active</span>'
                        : '<span style="display:inline-flex;align-items:center;gap:6px;color:#64748b;font-weight:600;background:#f1f5f9;padding:4px 12px;border-radius:6px;"><span style="width:8px;height:8px;border-radius:50%;background:#94a3b8;"></span>Inactive</span>'}
                </p>
                ${colourName ? `
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Colour</strong><br>
                    <span style="display:inline-flex;align-items:center;gap:6px;color:#0f172a;font-weight:600;"><span style="width:16px;height:16px;border-radius:4px;background:${colourHex};border:1px solid #d1d5db;display:inline-block;"></span> ${colourName}</span>
                </p>` : ''}
            </div>

            <hr style="border:0;border-top:1px solid #e8ecf1;margin:16px 0;">

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Product Code</strong><br>
                    <span style="color:#0f172a;font-weight:600;">${val(record.product_code)}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">SKU</strong><br>
                    <span style="color:#0f172a;">${val(record.sku)}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">HSN Code</strong><br>
                    <span style="color:#0f172a;">${val(record.hsn_code)}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Size</strong><br>
                    <span style="color:#0f172a;font-weight:600;">${val(record.pro_size)}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">UOM (Unit)</strong><br>
                    <span style="color:#0f172a;font-weight:600;">${uomName}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Packet Item</strong><br>
                    <span style="color:#0f172a;">${record.is_packet ? 'Yes' : 'No'}</span>
                </p>
            </div>

            ${record.product_des ? `
            <hr style="border:0;border-top:1px solid #e8ecf1;margin:16px 0;">
            <div style="margin-bottom:8px;">
                <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">📄 Description</strong>
            </div>
            <p style="margin:0;color:#0f172a;white-space:pre-wrap;font-size:13px;line-height:1.7;background:#f8fafc;padding:14px;border-radius:8px;border:1px solid #e8ecf1;">${val(record.product_des)}</p>
            ` : ''}
        `;

        document.getElementById('viewModalContent').innerHTML = content;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection
