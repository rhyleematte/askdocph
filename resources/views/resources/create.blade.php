@extends('layouts.dashboard')

@section('title', 'Create Resource – AskDocPH')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/resources.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Roboto:wght@400;500;700&family=Playfair+Display:wght@700&family=Lato:wght@400;700&family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600;700&family=Merriweather:wght@400;700&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* Font Face Classes for Quill */
    .ql-font-open-sans { font-family: 'Open Sans', sans-serif; }
    .ql-font-roboto { font-family: 'Roboto', sans-serif; }
    .ql-font-playfair-display { font-family: 'Playfair Display', serif; }
    .ql-font-lato { font-family: 'Lato', sans-serif; }
    .ql-font-poppins { font-family: 'Poppins', sans-serif; }
    .ql-font-inter { font-family: 'Inter', sans-serif; }
    .ql-font-merriweather { font-family: 'Merriweather', serif; }
    .ql-font-quicksand { font-family: 'Quicksand', sans-serif; }

    /* Size Classes */
    .ql-size-12px { font-size: 12px; }
    .ql-size-14px { font-size: 14px; }
    .ql-size-16px { font-size: 16px; }
    .ql-size-18px { font-size: 18px; }
    .ql-size-20px { font-size: 20px; }
    .ql-size-24px { font-size: 24px; }
    .ql-size-32px { font-size: 32px; }

    /* Hide Quill's native toolbar — we use our own */
    .ql-toolbar { display: none !important; }
    .ql-container.ql-snow { border: none !important; background: var(--panel-bg); min-height: 360px; font-size: 16px; font-family: 'Inter', sans-serif; }
    .ql-editor { padding: 20px 24px; line-height: 1.8; color: var(--text); min-height: 360px; }
    .ql-editor.ql-blank::before { color: var(--muted); font-style: normal; }
    .ql-editor a { color: var(--res-primary); }
    .ql-editor h1 { font-size: 28px; font-weight: 800; }
    .ql-editor h2 { font-size: 22px; font-weight: 700; }
    .ql-editor blockquote { border-left: 4px solid var(--res-primary); padding: 8px 16px; background: var(--hover); border-radius: 0 8px 8px 0; margin: 0; }
    .ql-editor img { max-width: 100%; border-radius: 8px; margin: 8px 0; display: block; }
    .ql-editor video { max-width: 100%; border-radius: 8px; margin: 8px 0; display: block; }
    .ql-editor audio { max-width: 100%; margin: 8px 0; display: block; width: 100%; }
    /* ── Composer Shell ─────────────────────────────────────── */
    .composer-shell {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background: var(--bg);
    }
    .composer-wrap {
      flex: 1;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 24px 16px 100px;
    }
    .composer-card {
      width: 100%;
      max-width: 860px;
      background: var(--panel-bg);
      border-radius: 20px;
      border: 1px solid var(--border);
      box-shadow: 0 8px 40px rgba(0,0,0,0.12);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    /* ── Composer Header ────────────────────────────────────── */
    .composer-header {
      background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
      padding: 16px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .composer-header-title {
      color: #e0e7ff;
      font-size: 15px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .composer-header-title i { width: 18px; height: 18px; }
    .composer-discard {
      background: rgba(255,255,255,0.1);
      border: none;
      color: #c7d2fe;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
      text-decoration: none;
    }
    .composer-discard:hover { background: rgba(255,255,255,0.2); color: #fff; }

    /* ── Meta Fields ────────────────────────────────────────── */
    .composer-meta {
      padding: 0;
      border-bottom: 1px solid var(--border);
    }
    .composer-field {
      display: flex;
      align-items: center;
      border-bottom: 1px solid var(--border);
      padding: 0 20px;
      min-height: 48px;
    }
    .composer-field:last-child { border-bottom: none; }
    .composer-field-label {
      font-size: 13px;
      font-weight: 600;
      color: var(--muted);
      min-width: 90px;
      flex-shrink: 0;
    }
    .composer-field-input {
      flex: 1;
      border: none;
      background: transparent;
      color: var(--text);
      font-size: 14px;
      outline: none;
      padding: 12px 0;
      font-family: 'Inter', sans-serif;
    }
    .composer-field-input::placeholder { color: var(--muted); opacity: 0.7; }
    .composer-field-select {
      flex: 1;
      border: none;
      background: transparent;
      color: var(--text);
      font-size: 14px;
      outline: none;
      cursor: pointer;
      padding: 12px 0;
      font-family: 'Inter', sans-serif;
      -webkit-appearance: none;
    }
    .composer-field-row {
      display: flex;
      align-items: center;
      border-bottom: 1px solid var(--border);
      padding: 0;
    }
    .composer-field-row .composer-field {
      border-bottom: none;
      flex: 1;
    }
    .composer-field-row .divider {
      width: 1px;
      height: 36px;
      background: var(--border);
      flex-shrink: 0;
    }

    /* ── Body Editor ────────────────────────────────────────── */
    .composer-body {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    #quill-editor {
      min-height: 380px;
      font-size: 16px;
      font-family: 'Inter', sans-serif;
      color: var(--text);
      padding: 20px 24px;
      outline: none;
      line-height: 1.8;
      background: var(--panel-bg);
    }
    #quill-editor:empty:before {
      content: attr(data-placeholder);
      color: var(--muted);
      pointer-events: none;
    }
    #quill-editor a { color: var(--res-primary); text-decoration: underline; }
    #quill-editor img { max-width: 100%; border-radius: 8px; margin: 8px 0; display: block; cursor: pointer; }
    #quill-editor video { max-width: 100%; border-radius: 8px; margin: 8px 0; display: block; cursor: pointer; }
    #quill-editor audio { max-width: 100%; margin: 8px 0; display: block; width: 100%; cursor: pointer; }
    #quill-editor blockquote {
      border-left: 4px solid var(--res-primary);
      margin: 0;
      padding: 8px 16px;
      background: var(--hover);
      border-radius: 0 8px 8px 0;
    }
    #quill-editor ul, #quill-editor ol { padding-left: 24px; }
    #quill-editor h1 { font-size: 28px; font-weight: 800; margin: 16px 0 8px; }
    #quill-editor h2 { font-size: 22px; font-weight: 700; margin: 14px 0 6px; }
    #quill-editor h3 { font-size: 18px; font-weight: 600; margin: 12px 0 4px; }

    /* ── Attachment Chips ───────────────────────────────────── */
    .composer-attachments {
      padding: 8px 20px;
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      min-height: 0;
      border-top: 1px solid transparent;
      transition: border-color 0.2s;
    }
    .composer-attachments:not(:empty) { border-color: var(--border); }
    .attachment-chip {
      display: flex;
      align-items: center;
      gap: 6px;
      background: var(--hover);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 4px 10px 4px 8px;
      font-size: 12px;
      color: var(--text);
      font-weight: 500;
      max-width: 200px;
    }
    .attachment-chip .chip-icon { color: var(--res-primary); flex-shrink: 0; }
    .attachment-chip .chip-name {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 120px;
    }
    .attachment-chip .chip-remove {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--muted);
      padding: 0;
      display: flex;
      align-items: center;
      flex-shrink: 0;
      transition: color 0.15s;
    }
    .attachment-chip .chip-remove:hover { color: var(--danger); }

    /* ── Bottom Toolbar ─────────────────────────────────────── */
    .composer-toolbar {
      border-top: 1px solid var(--border);
      padding: 10px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      background: var(--panel-bg);
      position: sticky;
      bottom: 0;
    }
    .composer-toolbar.is-floating {
      position: fixed;
      left: 50%;
      transform: translateX(-50%);
      bottom: calc(16px + env(safe-area-inset-bottom));
      width: min(860px, calc(100% - 32px));
      border: 1px solid var(--border);
      border-radius: 16px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.22);
      z-index: 1000;
    }
    .toolbar-left {
      display: flex;
      align-items: center;
      gap: 2px;
      flex-wrap: wrap;
    }
    .toolbar-right {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }
    .tool-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--muted);
      width: 34px;
      height: 34px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.15s, color 0.15s;
      font-size: 13px;
      font-weight: 700;
      font-family: 'Inter', sans-serif;
      position: relative;
      flex-shrink: 0;
    }
    .tool-btn:hover { background: var(--hover); color: var(--text); }
    .tool-btn.active { background: #ede9fe; color: #7c3aed; }
    .tool-btn svg { width: 16px; height: 16px; pointer-events: none; }
    .tool-divider {
      width: 1px;
      height: 20px;
      background: var(--border);
      margin: 0 4px;
      flex-shrink: 0;
    }
    .tool-select {
      background: none;
      border: 1px solid var(--border);
      cursor: pointer;
      color: var(--text);
      padding: 6px 8px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 500;
      font-family: 'Inter', sans-serif;
      transition: background 0.15s, border-color 0.15s;
      max-width: 120px;
      flex-shrink: 0;
    }
    .tool-select:hover { background: var(--hover); border-color: var(--res-primary); }
    .tool-select:focus { outline: none; background: var(--hover); border-color: var(--res-primary); }
    .tool-color-input {
      width: 34px;
      height: 34px;
      border: 1px solid var(--border);
      border-radius: 6px;
      cursor: pointer;
      padding: 2px;
      flex-shrink: 0;
    }
    .tool-color-input:hover { border-color: var(--res-primary); }
    .publish-btn {
      background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 14px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(124,58,237,0.3);
      transition: all 0.2s;
      white-space: nowrap;
    }
    .publish-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(124,58,237,0.4); }
    .publish-btn svg { width: 16px; height: 16px; }

    /* ── Tooltip ────────────────────────────────────────────── */
    .tool-btn[data-tip]:hover::after {
      content: attr(data-tip);
      position: absolute;
      bottom: calc(100% + 6px);
      left: 50%;
      transform: translateX(-50%);
      background: #1e293b;
      color: #fff;
      font-size: 11px;
      font-weight: 500;
      padding: 4px 8px;
      border-radius: 6px;
      white-space: nowrap;
      pointer-events: none;
      z-index: 100;
    }

    /* ── Popups (Emoji) ─────────────────────────────── */
    .composer-popup {
      position: absolute;
      background: var(--panel-bg);
      border: 1px solid var(--border);
      border-radius: 14px;
      box-shadow: 0 12px 40px rgba(0,0,0,0.2);
      z-index: 200;
      display: none;
      animation: popIn 0.15s ease;
    }
    .composer-popup.open { display: block; }
    @keyframes popIn {
      from { opacity: 0; transform: translateY(6px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Emoji grid */
    .emoji-popup { bottom: calc(100% + 10px); left: 0; padding: 12px; width: 280px; }
    .emoji-search {
      width: 100%;
      border: 1px solid var(--border);
      background: var(--input-bg);
      color: var(--text);
      border-radius: 8px;
      padding: 7px 10px;
      font-size: 13px;
      outline: none;
      margin-bottom: 8px;
      box-sizing: border-box;
    }
    .emoji-grid {
      display: grid;
      grid-template-columns: repeat(8, 1fr);
      gap: 2px;
      max-height: 180px;
      overflow-y: auto;
    }
    .emoji-grid button {
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      border-radius: 6px;
      padding: 4px;
      transition: background 0.1s;
      line-height: 1;
    }
    .emoji-grid button:hover { background: var(--hover); }

    /* Thumbnail preview */
    #thumbPreview {
      display: none;
      max-height: 80px;
      border-radius: 8px;
      border: 1px solid var(--border);
      margin: 8px 20px;
      object-fit: cover;
    }

    /* Dark mode fix for select */
    .composer-field-select option { background: var(--panel-bg); }

    /* Hidden inputs */
    .hidden-file { display: none; }

    @media (max-width: 600px) {
      .composer-field-row { flex-direction: column; }
      .composer-field-row .divider { display: none; }
      .toolbar-left { gap: 1px; }
      .tool-btn { width: 30px; height: 30px; }
    }

    /* ── Media Resize & Align Toolbar ────────────────────────── */
    #mediaResizeBar {
      position: absolute;
      background: #1e293b;
      color: #fff;
      padding: 8px 12px;
      border-radius: 10px;
      display: none;
      align-items: center;
      gap: 12px;
      z-index: 1000;
      box-shadow: 0 8px 24px rgba(0,0,0,0.3);
      border: 1px solid rgba(255,255,255,0.1);
      user-select: none;
    }
    .mrb-section { display: flex; align-items: center; gap: 8px; border-right: 1px solid rgba(255,255,255,0.1); padding-right: 12px; }
    .mrb-section:last-child { border-right: none; padding-right: 0; }
    .mrb-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
    .mrb-preset {
      background: rgba(255,255,255,0.1);
      border: none;
      color: #fff;
      font-size: 12px;
      padding: 4px 8px;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .mrb-preset:hover { background: rgba(255,255,255,0.2); }
    .mrb-preset.active { background: var(--res-primary); }
    .mrb-slider-wrap { display: flex; align-items: center; gap: 8px; }
    #mediaWidthSlider { width: 80px; accent-color: var(--res-primary); }
    #mediaWidthLabel { font-size: 11px; font-weight: 700; min-width: 32px; }
    .mrb-align-group { display: flex; gap: 4px; }
    .mrb-align-btn {
      background: none;
      border: none;
      color: #94a3b8;
      width: 28px;
      height: 28px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
    }
    .mrb-align-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .mrb-align-btn.active { background: var(--res-primary); color: #fff; }
    .mrb-delete {
      background: none;
      border: none;
      color: #fca5a5;
      width: 28px;
      height: 28px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
      margin-left: 4px;
    }
    .mrb-delete:hover { background: rgba(239,68,68,0.18); color: #fecaca; }
    .mrb-delete svg { width: 16px; height: 16px; }
    .mrb-close {
      background: none;
      border: none;
      color: #94a3b8;
      font-size: 18px;
      cursor: pointer;
      margin-left: 4px;
    }
    .mrb-close:hover { color: #f87171; }

    /* Handles */
    .media-handle {
      position: fixed;
      width: 14px;
      height: 14px;
      background: #fff;
      border: 3px solid var(--res-primary);
      border-radius: 50%;
      cursor: nwse-resize;
      z-index: 10001;
      display: none;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      transition: transform 0.1s ease;
      pointer-events: auto;
      touch-action: none;
    }
    .media-handle:hover { transform: scale(1.2); }
    .media-handle.tr { cursor: nesw-resize; }
    .media-handle.bl { cursor: nesw-resize; }
    .selected-media { outline: 2px solid var(--res-primary); outline-offset: 4px; border-radius: 2px; }
    body.is-resizing { cursor: nwse-resize !important; user-select: none; }
  </style>
@endpush

@section('content')
<div class="composer-shell">

  <div class="composer-wrap">
    <div class="composer-card">

      {{-- Header --}}
      <div class="composer-header">
        <div class="composer-header-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          New Resource
        </div>
        <a href="{{ route('resources.index') }}" class="composer-discard" title="Discard">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </a>
      </div>

      {{-- Form --}}
      <form action="{{ route('resources.store') }}" method="POST" enctype="multipart/form-data" id="resourceForm" accept-charset="utf-8">
        @csrf

        {{-- Meta fields --}}
        <div class="composer-meta">
          {{-- Title --}}
          <div class="composer-field">
            <span class="composer-field-label">Title</span>
            <input type="text" name="title" class="composer-field-input" required maxlength="50"
                   placeholder="Resource title (max 50 chars)">
          </div>

          {{-- Type + Duration --}}
          <div class="composer-field-row">
            <div class="composer-field">
              <span class="composer-field-label">Type</span>
              <select name="type" id="resTypeSelect" class="composer-field-select" required>
                <option value="Article">📄 Article</option>
                <option value="Audio">🎵 Audio</option>
                <option value="Workbook">📓 Workbook</option>
                <option value="Media">🖼️ Media</option>
                <option value="Video">🎬 Video</option>
              </select>
            </div>
            <div class="divider"></div>
            <div class="composer-field">
              <span class="composer-field-label">Duration</span>
              <input type="text" name="duration_meta" class="composer-field-input"
                     placeholder="e.g. 5 min read">
            </div>
          </div>

          {{-- Description --}}
          <div class="composer-field">
            <span class="composer-field-label">Summary</span>
            <input type="text" name="description" class="composer-field-input" required
                   placeholder="Short description shown on the card...">
          </div>

          {{-- Hashtags --}}
          <div class="composer-field">
            <span class="composer-field-label">Tags</span>
            <input type="text" name="hashtags" class="composer-field-input"
                   placeholder="anxiety, coping, stress (comma separated)">
          </div>
        </div>

        {{-- Hidden inputs --}}
        <input type="hidden" name="content" id="contentInput">


        {{-- Cover photo (thumbnail used on cards and detail header) --}}
        <div class="composer-field">
          <span class="composer-field-label">Cover photo</span>
          <div style="display:flex;align-items:center;gap:10px;flex:1;flex-wrap:wrap;">
            <button type="button" class="chip-btn" style="border-radius:999px;padding:6px 14px;font-size:13px;"
                    onclick="document.getElementById('thumbnailInput').click()">
              <i data-lucide="image"></i> Choose image
            </button>
            <span id="thumbFileName" style="font-size:13px;color:var(--muted);">No image selected</span>
          </div>
        </div>

        {{-- Cover preview --}}
        <img id="thumbPreview" src="" alt="Cover preview">

        {{-- Rich Body Editor (Quill) --}}
        <div class="composer-body">
          <div id="quill-editor"></div>
        </div>

        {{-- Attachment Chips --}}
        <div class="composer-attachments" id="attachmentChips"></div>

        {{-- Bottom Toolbar --}}
        <div class="composer-toolbar" id="composerToolbar">
          <div class="toolbar-left">

            {{-- ROW 1: Font Family & Size --}}
            <select id="fontSelect" class="tool-select" onchange="setFont(this.value)" data-tip="Font family">
              <option value="">Default</option>
              <option value="open-sans">Open Sans</option>
              <option value="roboto">Roboto</option>
              <option value="playfair-display">Playfair Display</option>
              <option value="lato">Lato</option>
              <option value="poppins">Poppins</option>
              <option value="inter">Inter</option>
              <option value="merriweather">Merriweather</option>
              <option value="quicksand">Quicksand</option>
            </select>

            <select id="sizeSelect" class="tool-select" onchange="setSize(this.value)" data-tip="Font size">
              <option value="">Size</option>
              <option value="12px">12px</option>
              <option value="14px">14px</option>
              <option value="16px">16px</option>
              <option value="18px">18px</option>
              <option value="20px">20px</option>
              <option value="24px">24px</option>
              <option value="32px">32px</option>
            </select>

            <div class="tool-divider"></div>

            {{-- Text Styles --}}
            <button type="button" class="tool-btn" id="btnBold"   data-tip="Bold (Ctrl+B)"       onclick="quillFormat('bold')"><b>B</b></button>
            <button type="button" class="tool-btn" id="btnItalic" data-tip="Italic (Ctrl+I)"     onclick="quillFormat('italic')"><i style="font-style:italic;">I</i></button>
            <button type="button" class="tool-btn" id="btnUnder"  data-tip="Underline (Ctrl+U)"  onclick="quillFormat('underline')"><u>U</u></button>
            <button type="button" class="tool-btn" id="btnStrike" data-tip="Strikethrough"        onclick="quillFormat('strike')"><s>S</s></button>

            <button type="button" class="tool-btn" id="btnClearFormat" data-tip="Clear formatting" onclick="quill.removeFormat(quill.getSelection().index, quill.getSelection().length); updateActiveStates();">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6v12M16 6v12M5.5 20h13"/></svg>
            </button>

            <div class="tool-divider"></div>

            {{-- ROW 2: Colors --}}
            <input type="color" id="textColorPicker" class="tool-color-input" title="Text color" value="#000000" onchange="setTextColor(this.value)">
            <input type="color" id="bgColorPicker" class="tool-color-input" title="Background color" value="#ffffff" onchange="setBackgroundColor(this.value)">

            <div class="tool-divider"></div>

            {{-- ROW 3: Structure --}}
            <button type="button" class="tool-btn" id="btnH1" data-tip="Heading 1" onclick="quillHeader(1)">H1</button>
            <button type="button" class="tool-btn" id="btnH2" data-tip="Heading 2" onclick="quillHeader(2)">H2</button>
            <button type="button" class="tool-btn" id="btnH3" data-tip="Heading 3" onclick="quillHeader(3)">H3</button>

            <button type="button" class="tool-btn" id="btnBullet"   data-tip="Bullet list"    onclick="quillList('bullet')">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </button>
            <button type="button" class="tool-btn" id="btnOrdered" data-tip="Numbered list"   onclick="quillList('ordered')">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
            </button>
            <button type="button" class="tool-btn" id="btnQuote" data-tip="Blockquote" onclick="quillBlockquote()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>
            </button>

            <button type="button" class="tool-btn" id="btnAlignLeft" data-tip="Align left" onclick="setAlign('left')">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
            </button>
            <button type="button" class="tool-btn" id="btnAlignCenter" data-tip="Align center" onclick="setAlign('center')">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="10" x2="5" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="19" y1="18" x2="5" y2="18"/></svg>
            </button>
            <button type="button" class="tool-btn" id="btnAlignRight" data-tip="Align right" onclick="setAlign('right')">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="10" x2="21" y2="10"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="14" x2="21" y2="14"/><line x1="7" y1="18" x2="21" y2="18"/></svg>
            </button>
            <button type="button" class="tool-btn" id="btnAlignJustify" data-tip="Align justify" onclick="setAlign('justify')">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="3" y2="18"/></svg>
            </button>

            <div class="tool-divider"></div>

            {{-- ROW 4: Insert Link --}}
            <button type="button" class="tool-btn" id="btnLink" data-tip="Insert link" onclick="insertLink()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            </button>

            {{-- Insert Photo (inline) --}}
            <button type="button" class="tool-btn" data-tip="Insert photo" onclick="document.getElementById('photoInput').click()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </button>

            {{-- Attach File --}}
            <button type="button" class="tool-btn" data-tip="Attach file" onclick="document.getElementById('attachInput').click()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            </button>

            {{-- Primary Media (PDF/Audio/Video) --}}
            <button type="button" class="tool-btn" data-tip="Upload document / media" id="btnPrimaryMedia" onclick="document.getElementById('fileInput').click()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </button>

            {{-- Add Audio (inline) --}}
            <button type="button" class="tool-btn" data-tip="Add Audio" onclick="document.getElementById('audioInput').click()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
            </button>

            {{-- Add Video (inline) --}}
            <button type="button" class="tool-btn" data-tip="Add Video" onclick="document.getElementById('videoInput').click()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
            </button>

            {{-- Insert Emoji --}}
            <div style="position: relative;">
              <button type="button" class="tool-btn" data-tip="Insert emoji" onclick="togglePopup('emojiPopup')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
              </button>
              <div class="composer-popup emoji-popup" id="emojiPopup" onmousedown="event.stopPropagation()">
                <input class="emoji-search" type="text" placeholder="Search emoji..." id="emojiSearch" oninput="filterEmojis(this.value)">
                <div class="emoji-grid" id="emojiGrid"></div>
              </div>
            </div>

            {{-- Set Thumbnail (card image) --}}
            <button type="button" class="tool-btn" data-tip="Set Cover photo" onclick="document.getElementById('thumbnailInput').click()">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </button>

          </div>

          <div class="toolbar-right">
            <button type="submit" class="publish-btn" id="publishBtn">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Publish
            </button>
          </div>
        </div>

      </form>

      {{-- Relocated hidden file inputs (now properly hidden via fixed CSS) --}}
      <input type="file" name="file" id="fileInput" class="hidden-file" form="resourceForm">
      <input type="file" name="thumbnail" id="thumbnailInput" class="hidden-file" accept="image/*" form="resourceForm">
      <input type="file" id="photoInput" class="hidden-file" accept="image/*">
      <input type="file" id="attachInput" class="hidden-file" multiple>
      <input type="file" id="videoInput" class="hidden-file" accept="video/*">
      <input type="file" id="audioInput" class="hidden-file" accept="audio/*">
    </div>
  </div>

  {{-- Link popup is now inline in the toolbar --}}
</div>

<div id="dash-toast" style="
  position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
  background: #1e293b; color: #fff; padding: 10px 20px; border-radius: 10px;
  font-size: 14px; font-weight: 500; z-index: 9999;
  opacity: 0; pointer-events: none; transition: opacity 0.2s;
  white-space: nowrap; box-shadow: 0 4px 20px rgba(0,0,0,0.3);
"></div>

<div id="uploadModal" style="display:none;position:fixed;inset:0;z-index:10010;align-items:center;justify-content:center;background:rgba(0,0,0,0.45);">
  <div style="width:min(420px,90%);background:var(--panel-bg);border-radius:18px;padding:22px;box-shadow:0 18px 50px rgba(0,0,0,0.35);text-align:center;">
    <div id="uploadModalMessage" style="font-weight:700;margin-bottom:14px;letter-spacing:0.02em;">Uploading file…</div>
    <div style="width:40px;height:40px;margin:0 auto;border:4px solid rgba(0,0,0,0.12);border-top-color:var(--res-primary);border-radius:50%;animation:spin 0.9s linear infinite;"></div>
  </div>
</div>

<style>@keyframes spin{to{transform:rotate(360deg);}}</style>

<style>

/* Resize handles */
.media-handle {
  position: fixed;
  width: 12px;
  height: 12px;
  background: #7c3aed;
  border: 2px solid #fff;
  border-radius: 50%;
  z-index: 10000;
  display: none;
  cursor: nwse-resize;
  box-shadow: 0 0 5px rgba(0,0,0,0.3);
  touch-action: none;
}
.media-handle.tr, .media-handle.bl { cursor: nesw-resize; }
</style>

<div id="mediaResizeBar">

<div id="handleNW" class="media-handle tl"></div>
<div id="handleNE" class="media-handle tr"></div>
<div id="handleSW" class="media-handle bl"></div>
<div id="handleSE" class="media-handle br"></div>

  {{-- Row 1: Size --}}
  <div class="mrb-section">
    <span class="mrb-label">Size</span>
    <button type="button" class="mrb-preset" onclick="setMediaWidth(25)">25%</button>
    <button type="button" class="mrb-preset" onclick="setMediaWidth(50)">50%</button>
    <button type="button" class="mrb-preset" onclick="setMediaWidth(75)">75%</button>
    <button type="button" class="mrb-preset" onclick="setMediaWidth(100)">100%</button>
    <div class="mrb-slider-wrap">
      <input type="range" id="mediaWidthSlider" min="10" max="100" value="100" oninput="setMediaWidth(this.value)">
      <span id="mediaWidthLabel">100%</span>
    </div>
    <button type="button" class="mrb-delete" onclick="deleteSelectedMedia()" title="Delete">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="3 6 5 6 21 6"/>
        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
        <path d="M10 11v6"/>
        <path d="M14 11v6"/>
        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
      </svg>
    </button>
    <button type="button" class="mrb-close" onclick="closeResizeBar()" title="Close">&times;</button>
  </div>

  {{-- Row 2: Align --}}
  <div class="mrb-section">
    <span class="mrb-label">Align</span>
    <div class="mrb-align-group">

      {{-- Align Left --}}
      <button type="button" class="mrb-align-btn" id="alignLeft" onclick="setMediaAlign('left')" title="Align Left">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/>
        </svg>
      </button>

      {{-- Align Center --}}
      <button type="button" class="mrb-align-btn" id="alignCenter" onclick="setMediaAlign('center')" title="Align Center">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
        </svg>
      </button>

      {{-- Align Right --}}
      <button type="button" class="mrb-align-btn" id="alignRight" onclick="setMediaAlign('right')" title="Align Right">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="9" y1="12" x2="21" y2="12"/><line x1="6" y1="18" x2="21" y2="18"/>
        </svg>
      </button>

    </div>
    <span style="font-size:11px;color:#475569;margin-left:auto;">Click image/video/audio to resize</span>
  </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.4.120/build/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mammoth@1.4.19/mammoth.browser.min.js"></script>
<script>
// ── Custom VideoBlot (so <video> renders inside Quill) ───────
const BlockEmbed = Quill.import('blots/block/embed');
class VideoBlot extends BlockEmbed {
  static create(value) {
    const node = super.create();
    node.setAttribute('src', typeof value === 'string' ? value : value.src);
    node.setAttribute('controls', 'controls');
    node.setAttribute('preload', 'metadata');
    node.style.maxWidth = (typeof value === 'object' && value.width) ? value.width : '100%';
    node.style.width = '100%';
    node.style.borderRadius = '12px';
    node.style.margin = '8px 0';
    node.style.display = 'block';
    node.style.backgroundColor = '#000';
    return node;
  }
  static value(node) { return { src: node.getAttribute('src'), width: node.style.width }; }
}
VideoBlot.blotName = 'htmlVideo'; VideoBlot.tagName = 'video';
Quill.register(VideoBlot);

class AudioBlot extends BlockEmbed {
  static create(value) {
    const node = super.create();
    node.setAttribute('src', typeof value === 'string' ? value : value.src);
    node.setAttribute('controls', 'controls');
    node.setAttribute('preload', 'metadata');
    node.style.width = '100%';
    node.style.maxWidth = '100%';
    node.style.margin = '8px 0';
    node.style.display = 'block';
    return node;
  }
  static value(node) { return { src: node.getAttribute('src'), width: node.style.width }; }
}
AudioBlot.blotName = 'htmlAudio'; AudioBlot.tagName = 'audio';
Quill.register(AudioBlot);

// ── Quill init ───────────────────────────────────────────────
const FontAttributor = Quill.import('formats/font');
FontAttributor.whitelist = ['open-sans','roboto','playfair-display','lato','poppins','inter','merriweather','quicksand'];
Quill.register(FontAttributor, true);

const SizeStyle = Quill.import('attributors/style/size');
SizeStyle.whitelist = ['12px','14px','16px','18px','20px','24px','32px'];
Quill.register(SizeStyle, true);

const quill = new Quill('#quill-editor', {
  theme: 'snow',
  placeholder: 'Write your resource content here… or upload a PDF/Doc to auto-fill.',
  modules: {
    toolbar: false,
    history: { delay: 400, maxStack: 200 }
  },
  formats: ['font', 'size', 'bold', 'italic', 'underline', 'strike', 'header', 'list', 'blockquote', 'link', 'image', 'htmlVideo', 'htmlAudio', 'color', 'background', 'align']
});

// ── Media resize overlay ──────────────────────────────────────
let selectedMedia = null;
const resizeBar = document.getElementById('mediaResizeBar');
const widthSlider = document.getElementById('mediaWidthSlider');
const widthLabel  = document.getElementById('mediaWidthLabel');
const supportsPointer = typeof window !== 'undefined' && 'PointerEvent' in window;
const editorDownEvent = supportsPointer ? 'pointerdown' : 'mousedown';
const handleDownEvent = supportsPointer ? 'pointerdown' : 'mousedown';
const moveEvent = supportsPointer ? 'pointermove' : 'mousemove';
const upEvent = supportsPointer ? 'pointerup' : 'mouseup';
const cancelEvent = supportsPointer ? 'pointercancel' : null;

function blurQuillEditor() {
  try {
    if (quill && typeof quill.setSelection === 'function') quill.setSelection(null);
  } catch (_) {}
  try {
    if (typeof quill.blur === 'function') { quill.blur(); }
  } catch (_) {}
  try {
    if (quill && quill.root && typeof quill.root.blur === 'function') quill.root.blur();
  } catch (_) {}
  try {
    const active = document.activeElement;
    if (active && active.nodeType === 1 && active.isContentEditable && typeof active.blur === 'function') active.blur();
  } catch (_) {}
  try {
    const sel = (typeof window !== 'undefined' && typeof window.getSelection === 'function') ? window.getSelection() : null;
    if (sel && typeof sel.removeAllRanges === 'function') sel.removeAllRanges();
  } catch (_) {}
}

// Some browsers (notably mobile Safari) can keep the contenteditable focused and
// prevent typing in regular inputs after interacting with the editor. Blurring
// Quill on outside interactions avoids that.
document.addEventListener(editorDownEvent, function(e) {
  const t = e.target;
  if (!t || t.nodeType !== 1) return;
  if (t.closest('.ql-editor') || t.closest('#composerToolbar') || t.closest('.composer-popup') || t.closest('#mediaResizeBar') || t.closest('.media-handle')) return;
  blurQuillEditor();
}, true);

// Extra safeguard: after typing in the contenteditable editor, some browsers can
// fail to move focus to regular form fields (meta "tiles"). We blur Quill and
// (re)focus the intended control on click/tap so typing works reliably.
const resourceForm = document.getElementById('resourceForm');
if (resourceForm) {
  const focusableFormControlSelector = 'input:not([type="hidden"]):not([type="file"]):not([disabled]), textarea:not([disabled]), select:not([disabled])';
  const metaArea = resourceForm.querySelector('.composer-meta');
  let pendingFocusEl = null;
  let focusLockEl = null;

  function isInEditorUi(el) {
    if (!el || el.nodeType !== 1 || typeof el.closest !== 'function') return false;
    return !!(el.closest('.ql-editor') || el.closest('#composerToolbar') || el.closest('.composer-popup') || el.closest('#mediaResizeBar') || el.closest('.media-handle'));
  }

  function findFocusableControl(el) {
    if (!el || el.nodeType !== 1 || typeof el.closest !== 'function') return null;

    // If user taps anywhere inside a meta "tile" row, focus the row's control.
    if (metaArea) {
      const metaField = el.closest('.composer-field');
      if (metaField && metaArea.contains(metaField)) {
        const rowControl = metaField.querySelector(focusableFormControlSelector);
        if (rowControl) return rowControl;
      }
    }

    // Otherwise, if a control itself (or its child) was targeted, use it.
    return el.closest(focusableFormControlSelector);
  }

  function lockFocus(control) {
    if (!control) return;
    focusLockEl = control;
  }

  function clearFocusLock() {
    focusLockEl = null;
  }

  function hasFocusLock() {
    if (!focusLockEl) return false;
    if (!document.contains(focusLockEl)) { focusLockEl = null; return false; }
    return true;
  }

  function safeFocus(control) {
    if (!control) return;

    try { control.focus({ preventScroll: true }); }
    catch (_) { try { control.focus(); } catch (_) {} }

    try {
      if (control.tagName === 'INPUT' || control.tagName === 'TEXTAREA') {
        const len = (control.value || '').length;
        control.setSelectionRange(len, len);
      }
    } catch (_) {}
  }

  function ensureFocus(control) {
    if (!control) return;
    safeFocus(control);
    setTimeout(() => {
      if (!document.contains(control)) return;
      if (document.activeElement !== control) safeFocus(control);
    }, 0);
  }

  // If Quill tries to steal focus while a tile control is locked, force it back.
  if (quill && quill.root) {
    ['pointerdown', 'touchstart', 'mousedown'].forEach(evtName => {
      quill.root.addEventListener(evtName, function() { clearFocusLock(); }, true);
    });

    quill.root.addEventListener('focusin', function(e) {
      if (!hasFocusLock()) return;
      blurQuillEditor();
      ensureFocus(focusLockEl);
      try { e.stopImmediatePropagation(); } catch (_) { e.stopPropagation(); }
    }, true);
  }

  function primeTileFocus(e) {
    const t = e.target;
    if (!t || t.nodeType !== 1) return;
    if (isInEditorUi(t)) return;

    const control = findFocusableControl(t);
    if (!control) return;

    pendingFocusEl = control;
    lockFocus(control);
    blurQuillEditor();
  }

  function applyTileFocus(e) {
    const t = e.target;
    if (!t || t.nodeType !== 1) return;
    if (isInEditorUi(t)) return;

    const control = pendingFocusEl || findFocusableControl(t);
    pendingFocusEl = null;
    if (!control) return;

    lockFocus(control);
    blurQuillEditor();
    ensureFocus(control);
  }

  // Blur early on touch/pointer/mouse down, then force-focus on release/click (mobile + desktop).
  ['pointerdown', 'touchstart', 'mousedown'].forEach(evtName => {
    resourceForm.addEventListener(evtName, primeTileFocus, true);
  });
  ['pointerup', 'touchend', 'mouseup', 'click'].forEach(evtName => {
    resourceForm.addEventListener(evtName, applyTileFocus, true);
  });

  // If a control does get focused, ensure Quill is blurred as well.
  resourceForm.addEventListener('focusin', function(e) {
    const t = e.target;
    if (!t || t.nodeType !== 1 || typeof t.matches !== 'function') return;
    if (!t.matches(focusableFormControlSelector)) return;
    if (isInEditorUi(t)) return;
    lockFocus(t);
    blurQuillEditor();
  }, true);
}

function eventPathHasSelector(e, selector) {
  if (!e) return false;

  const target = e.target;
  if (target && target.nodeType === 1 && typeof target.closest === 'function') {
    if (target.closest(selector)) return true;
  }

  if (typeof e.composedPath !== 'function') return false;
  const path = e.composedPath();
  for (const node of path) {
    if (node && node.nodeType === 1 && typeof node.matches === 'function' && node.matches(selector)) {
      return true;
    }
  }
  return false;
}

function getClosestMediaFromEvent(e) {
  const target = e && e.target;
  if (target && target.nodeType === 1 && typeof target.closest === 'function') {
    const el = target.closest('img, video, audio');
    if (el) return el;
  }

  if (!e || typeof e.composedPath !== 'function') return null;
  const path = e.composedPath();
  for (const node of path) {
    if (node && node.nodeType === 1 && typeof node.matches === 'function' && node.matches('img, video, audio')) {
      return node;
    }
  }
  return null;
}

function selectMedia(target) {
  if (!target) { closeResizeBar(); return false; }
  if (selectedMedia && selectedMedia !== target) selectedMedia.classList.remove('selected-media');
  selectedMedia = target;
  selectedMedia.classList.add('selected-media');
  attachHandleListeners();
  updateResizeUi();
  return true;
}

function selectMediaFromEvent(e) {
  const target = getClosestMediaFromEvent(e);
  if (!target) return false;
  selectMedia(target);
  return true;
}

// Use capture-phase pointer events so native <audio controls> clicks (shadow DOM)
// still select the <audio> element and show the resize UI.
quill.root.addEventListener(editorDownEvent, function(e) {
  selectMediaFromEvent(e);
}, true);

// Some browsers only fire touch events for audio controls.
quill.root.addEventListener('touchstart', function(e) {
  selectMediaFromEvent(e);
}, true);

// Capture click early to catch shadow DOM targets from audio controls.
quill.root.addEventListener('click', function(e) {
  selectMediaFromEvent(e);
}, true);

quill.root.addEventListener('click', function(e) {
  const target = getClosestMediaFromEvent(e);
  if (!target) {
    const t = e.target;
    if (t && t.nodeType === 1 && typeof t.closest === 'function' && t.closest('.ql-editor')) {
      closeResizeBar();
    }
    return;
  }
  const didSelect = selectMedia(target);
  if (didSelect) e.stopPropagation();
});

function attachHandleListeners() {
  ['handleNW', 'handleNE', 'handleSW', 'handleSE'].forEach(id => {
    const handle = document.getElementById(id);
    if (!handle) return;
    // Remove old listeners by cloning
    const newHandle = handle.cloneNode(true);
    handle.parentNode.replaceChild(newHandle, handle);

    document.getElementById(id).addEventListener(handleDownEvent, function(e) {
      if (!selectedMedia) return;
      isResizing = true;
      document.body.classList.add('is-resizing');
      activeHandle = id;
      startX = e.clientX;
      startWidth = selectedMedia.offsetWidth;
      const parentW = selectedMedia.parentElement.offsetWidth;
      startWidthPct = (startWidth / parentW) * 100;

      document.addEventListener(moveEvent, handleResizeMove);
      document.addEventListener(upEvent, handleResizeUp);
      if (cancelEvent) document.addEventListener(cancelEvent, handleResizeUp);
      if (supportsPointer && typeof e.pointerId === 'number' && typeof this.setPointerCapture === 'function') {
        try { this.setPointerCapture(e.pointerId); } catch (_) {}
      }
      e.preventDefault();
    }, { passive: false });
  });
}

function updateResizeUi() {
  if (!selectedMedia) return;
  const rect = selectedMedia.getBoundingClientRect();
  
  // Bar
  resizeBar.style.display = 'flex';
  resizeBar.style.position = 'fixed';
  resizeBar.style.zIndex = '10000';
  resizeBar.style.top  = Math.max(8, rect.bottom + 12) + 'px';
  resizeBar.style.left = Math.max(8, rect.left) + 'px';
  
  // Handles
  const handles = ['handleNW', 'handleNE', 'handleSW', 'handleSE'];
  handles.forEach(h => document.getElementById(h).style.display = 'block');
  
  const nw = document.getElementById('handleNW');
  const ne = document.getElementById('handleNE');
  const sw = document.getElementById('handleSW');
  const se = document.getElementById('handleSE');
  
  const hSize = 14; 
  const offset = hSize / 2;
  
  nw.style.top = (rect.top - offset) + 'px';
  nw.style.left = (rect.left - offset) + 'px';
  
  ne.style.top = (rect.top - offset) + 'px';
  ne.style.left = (rect.right - offset) + 'px';
  
  sw.style.top = (rect.bottom - offset) + 'px';
  sw.style.left = (rect.left - offset) + 'px';
  
  se.style.top = (rect.bottom - offset) + 'px';
  se.style.left = (rect.right - offset) + 'px';

  // Slider
  const curW = parseInt(selectedMedia.style.width) || 100;
  widthSlider.value = curW;
  widthLabel.textContent = curW + '%';
  syncActivePresets(curW);
}

// Update on scroll/resize
window.addEventListener('scroll', updateResizeUi, true);
window.addEventListener('resize', updateResizeUi);

// Dragging logic
let isResizing = false;
let startX, startWidth, startWidthPct, activeHandle;

function handleResizeMove(e) {
  if (!isResizing || !selectedMedia) return;
  const delta = e.clientX - startX;
  const parentW = selectedMedia.parentElement.offsetWidth;

  let newWidthPct;
  if (activeHandle === 'handleSE' || activeHandle === 'handleNE') {
    newWidthPct = startWidthPct + (delta / parentW) * 100;
  } else {
    newWidthPct = startWidthPct - (delta / parentW) * 100;
  }

  newWidthPct = Math.min(100, Math.max(10, newWidthPct));
  setMediaWidth(newWidthPct);
}

function handleResizeUp() {
  isResizing = false;
  activeHandle = null;
  document.body.classList.remove('is-resizing');
  document.removeEventListener(moveEvent, handleResizeMove);
  document.removeEventListener(upEvent, handleResizeUp);
  if (cancelEvent) document.removeEventListener(cancelEvent, handleResizeUp);
}

document.addEventListener('click', e => {
  if (!eventPathHasSelector(e, '#mediaResizeBar') && !eventPathHasSelector(e, '.ql-editor') && !eventPathHasSelector(e, '.media-handle')) {
    closeResizeBar();
  }
});

function setMediaWidth(pct) {
  if (!selectedMedia) return;
  pct = Math.round(pct);
  selectedMedia.style.width    = pct + '%';
  selectedMedia.style.maxWidth = pct + '%';
  widthSlider.value = pct;
  widthLabel.textContent = pct + '%';
  syncActivePresets(pct);
  updateResizeUi();
}
function syncActivePresets(pct) {
  resizeBar.querySelectorAll('.mrb-preset').forEach(b => {
    b.classList.toggle('active', b.textContent.trim() === pct + '%');
  });
}
function setMediaAlign(align) {
  if (!selectedMedia) return;
  // Reset all alignment styles first
  selectedMedia.style.float       = '';
  selectedMedia.style.marginLeft  = '';
  selectedMedia.style.marginRight = '';
  selectedMedia.style.display     = 'block';
  if (align === 'left') {
    selectedMedia.style.float = 'left';
    selectedMedia.style.marginRight = '16px';
  } else if (align === 'right') {
    selectedMedia.style.float = 'right';
    selectedMedia.style.marginLeft = '16px';
  } else { // center
    selectedMedia.style.marginLeft  = 'auto';
    selectedMedia.style.marginRight = 'auto';
  }
  // Highlight active align button
  ['alignLeft','alignCenter','alignRight'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.toggle('active',
      (align === 'left'   && id === 'alignLeft')  ||
      (align === 'center' && id === 'alignCenter') ||
      (align === 'right'  && id === 'alignRight') );
  });
}
function closeResizeBar() {
  resizeBar.style.display = 'none';
  const handles = ['handleNW', 'handleNE', 'handleSW', 'handleSE'];
  handles.forEach(h => document.getElementById(h).style.display = 'none');
  if (selectedMedia) { selectedMedia.classList.remove('selected-media'); selectedMedia = null; }
}

function deleteSelectedMedia() {
  if (!selectedMedia) return;

  try {
    const blot = Quill.find(selectedMedia);
    if (blot) {
      const index = quill.getIndex(blot);
      quill.deleteText(index, 1, 'user');
    } else {
      selectedMedia.remove();
      quill.update('user');
    }
  } catch (_) {}

  closeResizeBar();
}

// ── Quill formatting helpers ─────────────────────────────────
function quillFormat(fmt) {
  const current = quill.getFormat();
  quill.format(fmt, !current[fmt]);
  updateActiveStates();
}

function quillHeader(level) {
  const current = quill.getFormat();
  quill.format('header', current.header === level ? false : level);
  updateActiveStates();
}

function quillList(type) {
  const current = quill.getFormat();
  quill.format('list', current.list === type ? false : type);
  updateActiveStates();
}

function quillBlockquote() {
  const current = quill.getFormat();
  quill.format('blockquote', !current.blockquote);
  updateActiveStates();
}

// ── Font & Size formatting ────────────────────────────────────
function setFont(font) {
  if (font) {
    quill.format('font', font);
  } else {
    quill.removeFormat(quill.getSelection().index, quill.getSelection().length, 'font');
  }
  updateActiveStates();
}

function setSize(size) {
  if (size) {
    quill.format('size', size);
  } else {
    quill.removeFormat(quill.getSelection().index, quill.getSelection().length, 'size');
  }
  updateActiveStates();
}

// ── Color formatting ──────────────────────────────────────────
function setTextColor(color) {
  quill.format('color', color);
  updateActiveStates();
}

function setBackgroundColor(color) {
  quill.format('background', color);
  updateActiveStates();
}

// ── Text alignment ────────────────────────────────────────────
function setAlign(align) {
  const fmt = quill.getFormat();
  if (fmt.align === align) {
    quill.format('align', false);
  } else {
    quill.format('align', align);
  }
  updateActiveStates();
  // Update active state for alignment buttons
  ['btnAlignLeft', 'btnAlignCenter', 'btnAlignRight', 'btnAlignJustify'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.classList.remove('active');
    }
  });
  if (align === 'left') document.getElementById('btnAlignLeft').classList.add('active');
  else if (align === 'center') document.getElementById('btnAlignCenter').classList.add('active');
  else if (align === 'right') document.getElementById('btnAlignRight').classList.add('active');
  else if (align === 'justify') document.getElementById('btnAlignJustify').classList.add('active');
}

// ── Link insertion ────────────────────────────────────────────
function insertLink() {
  const selection = quill.getSelection();
  if (!selection || selection.length === 0) {
    alert('Please select some text to create a link');
    return;
  }
  const url = prompt('Enter the URL:');
  if (url) {
    const normalized = normalizeLinkUrl(url);
    quill.format('link', normalized);
    updateActiveStates();
  }
}

function normalizeLinkUrl(url) {
  // Normalize bare domains (e.g. google.com) to open as external URLs instead of relative paths.
  // Treat paths like "docs/file.pdf" as root-relative so they don't become relative to the current resource URL.
  const trimmed = url.trim();
  if (/^(https?:\/\/|mailto:|tel:|ftp:\/\/|\/\/)/i.test(trimmed)) {
    return trimmed;
  }
  if (trimmed.startsWith('/') || trimmed.startsWith('./') || trimmed.startsWith('../')) {
    return trimmed;
  }
  const firstSegment = trimmed.split('/')[0];
  if (firstSegment.includes('.')) {
    return 'https://' + trimmed;
  }
  return '/' + trimmed;
}

// ── Active state tracking ─────────────────────────────────────
quill.on('selection-change', updateActiveStates);
quill.on('text-change', updateActiveStates);

function updateActiveStates() {
  const fmt = quill.getFormat() || {};
  const map = [
    ['btnBold',    !!fmt.bold],
    ['btnItalic',  !!fmt.italic],
    ['btnUnder',   !!fmt.underline],
    ['btnStrike',  !!fmt.strike],
    ['btnH1',      fmt.header === 1],
    ['btnH2',      fmt.header === 2],
    ['btnH3',      fmt.header === 3],
    ['btnBullet',  fmt.list === 'bullet'],
    ['btnOrdered', fmt.list === 'ordered'],
    ['btnQuote',   !!fmt.blockquote],
  ];
  map.forEach(([id, on]) => {
    const el = document.getElementById(id);
    if (el) el.classList.toggle('active', on);
  });

  // Update font selector
  const fontSelect = document.getElementById('fontSelect');
  if (fontSelect && fmt.font) {
    fontSelect.value = fmt.font;
  } else if (fontSelect) {
    fontSelect.value = '';
  }

  // Update size selector
  const sizeSelect = document.getElementById('sizeSelect');
  if (sizeSelect && fmt.size) {
    sizeSelect.value = fmt.size;
  } else if (sizeSelect) {
    sizeSelect.value = '';
  }

  // Update alignment buttons
  ['btnAlignLeft', 'btnAlignCenter', 'btnAlignRight', 'btnAlignJustify'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
  });
  if (fmt.align === 'left') {
    const el = document.getElementById('btnAlignLeft');
    if (el) el.classList.add('active');
  } else if (fmt.align === 'center') {
    const el = document.getElementById('btnAlignCenter');
    if (el) el.classList.add('active');
  } else if (fmt.align === 'right') {
    const el = document.getElementById('btnAlignRight');
    if (el) el.classList.add('active');
  } else if (fmt.align === 'justify') {
    const el = document.getElementById('btnAlignJustify');
    if (el) el.classList.add('active');
  }
}


// Keyboard shortcuts
quill.keyboard.addBinding({ key: 'B', shortKey: true }, () => quillFormat('bold'));
quill.keyboard.addBinding({ key: 'I', shortKey: true }, () => quillFormat('italic'));
quill.keyboard.addBinding({ key: 'U', shortKey: true }, () => quillFormat('underline'));

// ── Popup management + Emoji ────────────────────────────────

function togglePopup(id) {
  const el = document.getElementById(id);
  if (!el) return;
  const isOpen = el.classList.contains('open');
  document.querySelectorAll('.composer-popup').forEach(p => p.classList.remove('open'));
  if (!isOpen && id === 'emojiPopup') {
    renderEmojis('');
    el.classList.add('open');
  }
}
function closePopup(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}
document.addEventListener('mousedown', e => {
  if (e.target.closest('.composer-popup')) {
    e.stopPropagation();
    return;
  }
  if (!e.target.closest('.tool-btn')) {
    document.querySelectorAll('.composer-popup').forEach(p => p.classList.remove('open'));
  }
});

// ── Emoji ────────────────────────────────────────────────────
const ALL_EMOJIS = [
  '😀','😂','🥲','😍','🤩','😎','🥳','🤯','😴','🥺','😭','🤣',
  '❤️','🧡','💛','💚','💙','💜','🖤','🤍','💯','✨','🔥','⭐',
  '🎉','🎊','🎁','🏆','💡','📚','📝','💼','🏥','🩺','🌱','🌸',
  '🌺','🌻','🌍','☀️','🌙','⚡','🌈','❄️','💪','🙌','👏','🤝',
  '👍','👎','🙏','✌️','🤞','🤙','👋','🫶','🧠','❓','‼️','✅',
  '❌','⚠️','📌','🔑','🔔','📣','💬','🔗'
];
function renderEmojis(query) {
  const filtered = query ? ALL_EMOJIS.filter(e => e.includes(query)) : [...ALL_EMOJIS];
  document.getElementById('emojiGrid').innerHTML = filtered.map(e =>
    `<button type="button" onclick="insertEmoji('${e}')">${e}</button>`
  ).join('');
}
function filterEmojis(q) { renderEmojis(q); }
function insertEmoji(emoji) {
  const range = quill.getSelection(true);
  quill.insertText(range ? range.index : quill.getLength(), emoji);
  closePopup('emojiPopup');
}

// ── Insert Inline Photo ──────────────────────────────────────
document.getElementById('photoInput').addEventListener('change', function(e) {
  const file = e.target.files[0]; if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    const range = quill.getSelection(true);
    quill.insertEmbed(range ? range.index : quill.getLength(), 'image', ev.target.result);
    toast('🖼️ Photo inserted!');
  };
  reader.readAsDataURL(file);
  this.value = '';
});

// ── Attach Files (chips) ─────────────────────────────────────
const attachedFiles = [];
let pendingUploads = 0;
const titleInput = document.querySelector('[name="title"]');
let lastAutoTitle = '';

function setTitleFromFileName(fileName) {
  if (!titleInput) return;
  const base = fileName.replace(/\.[^/.]+$/, '');
  if (!titleInput.value || titleInput.value === lastAutoTitle) {
    titleInput.value = base;
    lastAutoTitle = base;
  }
}

function makeFileLinkPlaceholder(file, href = 'about:blank') {
  const ext = file.name.split('.').pop().toLowerCase();
  const iconName = ext === 'pdf' ? 'file-text' : (ext === 'doc' || ext === 'docx') ? 'file-text' : ext === 'xml' ? 'file-text' : 'file';
  const icon = `<i data-lucide="${iconName}" style="width:16px;height:16px;" aria-hidden="true"></i>`;
  const label = `${icon} ${file.name}`;
  const style = 'display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border:1px solid rgba(0,0,0,0.12);border-radius:12px;background:rgba(99,102,241,0.1);color:var(--res-primary);text-decoration:none;font-weight:700;';
  return `<p><a href="${href}" class="file-link-placeholder" data-file-name="${file.name}" data-ext="${ext}" data-href="${href}" style="${style}">${label}</a>&#8203;</p>`;
}

function showUploadModal(message) {
  const modal = document.getElementById('uploadModal');
  const msg = document.getElementById('uploadModalMessage');
  if (!modal || !msg) return;
  msg.textContent = message;
  modal.style.display = 'flex';
}

function hideUploadModal() {
  const modal = document.getElementById('uploadModal');
  if (!modal) return;
  modal.style.display = 'none';
}

function insertUploadedFile(file, url) {
  const range = quill.getSelection(true);
  const idx = range ? range.index : quill.getLength();
  const link = makeFileLinkPlaceholder(file, url);
  quill.clipboard.dangerouslyPasteHTML(idx, link);
  moveCursorAfterLastFileLink();

  // Render any Lucide icons that may be inside the pasted HTML.
  if (window.lucide) lucide.createIcons({ root: document.getElementById('quill-editor') });

  // Also touch Quill's internal model so it serializes properly.
  try {
    const placeholder = document.querySelector(`.file-link-placeholder[data-file-name="${CSS.escape(file.name)}"]`);
    if (placeholder) {
      const blot = Quill.find(placeholder);
      if (blot) {
        const index = quill.getIndex(blot);
        const length = typeof blot.length === 'function' ? blot.length() : (placeholder.textContent || '').length;
        quill.formatText(index, length, 'link', url, 'user');
      }
    }
  } catch (_) {}
}

function moveCursorAfterLastFileLink() {
  const editor = document.getElementById('quill-editor');
  const last = editor.querySelector('.file-link-placeholder:last-of-type');
  if (!last) return;
  const range = document.createRange();
  range.setStartAfter(last);
  range.collapse(true);
  const sel = window.getSelection();
  sel.removeAllRanges();
  sel.addRange(range);
}

document.getElementById('attachInput').addEventListener('change', async function(e) {
  const files = Array.from(e.target.files);
  if (!files.length) return;

  for (const file of files) {
    attachedFiles.push(file);
    addAttachmentChip(file);

    const ext = file.name.split('.').pop().toLowerCase();
    const icon = (ext === 'mp3' || ext === 'wav') ? '🎵 ' : ext === 'mp4' ? '🎬 ' : ext === 'pdf' ? '📄 ' : '📎 ';

    if (['pdf','doc','docx','xml','xls','xlsx'].includes(ext)) {
      setTitleFromFileName(file.name);
      showUploadModal(`Uploading ${file.name}...`);

      try {
        const url = await new Promise((resolve, reject) => {
          uploadAttachedFile(file, (uploadedUrl) => {
            if (uploadedUrl) resolve(uploadedUrl);
            else reject(new Error('Upload returned no URL'));
          });
        });

        insertUploadedFile(file, url);
        toast('✅ File uploaded — click the link to open/download it.');
      } catch (err) {
        console.error('Upload failed', err);
        toast('❌ Upload failed. Please try again.');
      } finally {
        hideUploadModal();
      }
    } else {
      const range = quill.getSelection(true);
      const idx = range ? range.index : quill.getLength();
      quill.insertText(idx, '\n' + icon + file.name + '\n', { bold: false });
      quill.setSelection(idx + file.name.length + 3);
    }
  }

  this.value = '';
});

// Enable previewing attached files from the editor (opens PDFs in-browser, forces download for other types)
document.getElementById('quill-editor').addEventListener('click', async function(e) {
  const link = e.target.closest('.file-link-placeholder');
  if (!link) return;
  e.preventDefault();

  const fileName = link.dataset.fileName || '';
  const ext = (link.dataset.ext || fileName.split('.').pop() || '').toLowerCase();

  // Determine URL (support placeholders that may have already been saved to the server)
  let url = link.dataset.href || '';
  const href = link.getAttribute('href') || '';
  const isPlaceholderHref = href && /^(javascript:void\(0\)|about:blank|#)$/i.test(href);
  if (!url && href && !isPlaceholderHref) {
    url = href;
  }

  // Fallback: if the file was attached in this editor session, use a blob URL.
  if (!url && fileName) {
    const file = attachedFiles.find(f => f.name === fileName);
    if (file) {
      url = link.dataset.objectUrl || URL.createObjectURL(file);
      link.dataset.objectUrl = url;
    }
  }

  if (!url) return;

  // PDFs should be opened in a new tab (viewer)
  if (ext === 'pdf') {
    window.open(url, '_blank');
    return;
  }

  // Force download for non-PDF file types
  const download = async (downloadUrl) => {
    const a = document.createElement('a');
    a.href = downloadUrl;
    a.download = fileName || '';
    document.body.appendChild(a);
    a.click();
    a.remove();
  };

  const sameOriginOrBlob = url.startsWith('blob:') || url.startsWith(window.location.origin) || url.startsWith('/');
  if (sameOriginOrBlob) {
    download(url);
    return;
  }

  // Fallback: try fetching and forcing a download (CORS may prevent this)
  try {
    const resp = await fetch(url);
    const blob = await resp.blob();
    const blobUrl = URL.createObjectURL(blob);
    download(blobUrl);
    setTimeout(() => URL.revokeObjectURL(blobUrl), 60000);
  } catch (err) {
    window.open(url, '_blank');
  }
});

function addAttachmentChip(file) {
  const chip = document.createElement('div');
  chip.className = 'attachment-chip';
  chip.dataset.name = file.name;
  const ext = file.name.split('.').pop().toLowerCase();
  const iconName = ext === 'pdf' ? 'file-text' : (ext === 'mp3' || ext === 'wav') ? 'music' : ext === 'mp4' ? 'video' : 'paperclip';
  chip.innerHTML = `<span class="chip-icon"><i data-lucide="${iconName}" style="width:16px;height:16px;" aria-hidden="true"></i></span><span class="chip-name" title="${file.name}">${file.name}</span><button type="button" class="chip-remove" onclick="removeChip(this,'${file.name}')"><svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><line x1='18' y1='6' x2='6' y2='18'/><line x1='6' y1='6' x2='18' y2='18'/></svg></button>`;
  document.getElementById('attachmentChips').appendChild(chip);
  if (window.lucide) lucide.createIcons({ root: chip });
}
function removeChip(btn, name) {
  btn.closest('.attachment-chip').remove();
  const idx = attachedFiles.findIndex(f => f.name === name);
  if (idx > -1) attachedFiles.splice(idx, 1);

  // If the removed chip corresponds to the primary file input, clear it too.
  const fileInputEl = document.getElementById('fileInput');
  if (fileInputEl && fileInputEl.files && fileInputEl.files[0] && fileInputEl.files[0].name === name) {
    fileInputEl.value = '';
  }
}

function uploadAttachedFile(file, callback) {
  pendingUploads += 1;
  const formData = new FormData();
  formData.append('media', file);
  formData.append('_token', '{{ csrf_token() }}');

  fetch('{{ route("resources.upload-media") }}', {
    method: 'POST',
    body: formData,
  })
    .then(res => {
      if (!res.ok) throw new Error(`${res.status} ${res.statusText}`);
      return res.json();
    })
    .then(data => {
      if (data && data.success && data.url) {
        callback(data.url);
        toast('✅ File uploaded — click the link to open/download it.');
      } else {
        callback(null);
        toast('❌ Failed to upload file.');
      }
    })
    .catch(err => {
      console.error('Upload failed', err);
      callback(null);
      toast('❌ Upload error. ' + (err.message || '')); 
    })
    .finally(() => {
      pendingUploads = Math.max(0, pendingUploads - 1);
    });
}

// ── Add Video (inline at cursor via VideoBlot) ──────────────
document.getElementById('videoInput').addEventListener('change', function(e) {
  const file = e.target.files[0]; if (!file) return;
  toast('🎬 Uploading video...');

  const formData = new FormData();
  formData.append('media', file);
  formData.append('_token', '{{ csrf_token() }}');

  fetch('{{ route("resources.upload-media") }}', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const range = quill.getSelection(true);
      const idx = range ? range.index : quill.getLength();
      quill.insertEmbed(idx, 'htmlVideo', data.url);
      quill.insertText(idx + 1, '\n');
      quill.setSelection(idx + 2);
      addAttachmentChip(file);
      toast('🎬 Video inserted at cursor! Click it to resize.');
    } else {
      toast('❌ Failed to upload video');
    }
  })
  .catch(err => {
    console.error(err);
    toast('❌ Upload error');
  });

  this.value = '';
});

// ── Add Audio (inline at cursor via AudioBlot) ──────────────
document.getElementById('audioInput').addEventListener('change', function(e) {
  const file = e.target.files[0]; if (!file) return;
  toast('🎵 Uploading audio...');

  const formData = new FormData();
  formData.append('media', file);
  formData.append('_token', '{{ csrf_token() }}');

  fetch('{{ route("resources.upload-media") }}', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const range = quill.getSelection(true);
      const idx = range ? range.index : quill.getLength();
      quill.insertEmbed(idx, 'htmlAudio', data.url);
      quill.insertText(idx + 1, '\n');
      quill.setSelection(idx + 2);
      addAttachmentChip(file);
      toast('🎵 Audio inserted at cursor! Click it to resize.');
    } else {
      toast('❌ Failed to upload audio');
    }
  })
  .catch(err => {
    console.error(err);
    toast('❌ Upload error');
  });

  this.value = '';
});

// ── Set Thumbnail (card image) ─────────────────────────────
document.getElementById('thumbnailInput').addEventListener('change', function(e) {
  const file = e.target.files[0]; if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    const img = document.getElementById('thumbPreview');
    img.src = ev.target.result;
    img.style.display = 'block';
    toast('✅ Card thumbnail updated!');
  };
  reader.readAsDataURL(file);
  const label = document.getElementById('thumbFileName');
  if (label) label.textContent = file.name;
});

// ── Primary Media (fileInput) ─────────────────────────────────
const typeSelect = document.getElementById('resTypeSelect');
const fileInput  = document.getElementById('fileInput');
const btnPrimaryMedia = document.getElementById('btnPrimaryMedia');

typeSelect.addEventListener('change', function() {
  const type = this.value;
  fileInput.accept = type === 'Audio' ? 'audio/*' : type === 'Video' ? 'video/*' : '.pdf,.doc,.docx,.xml,.xls,.xlsx';
  btnPrimaryMedia.setAttribute('data-tip',
    type === 'Audio' ? 'Upload Audio (MP3, WAV)' :
    type === 'Video' ? 'Upload Video (MP4)' : 'Upload Document (PDF/DOCX/XML/XLSX)');
});
typeSelect.dispatchEvent(new Event('change'));

if (typeof pdfjsLib !== 'undefined') {
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.4.120/build/pdf.worker.min.js';
}

fileInput.addEventListener('change', function(e) {
  const file = e.target.files[0]; if (!file) return;
  if (!attachedFiles.some(f => f.name === file.name)) {
    attachedFiles.push(file);
  }
  addAttachmentChip(file);

  const ext = file.name.split('.').pop().toLowerCase();
  const reader = new FileReader();

  if (ext === 'pdf') {
    setTitleFromFileName(file.name);
    if (typeof pdfjsLib === 'undefined') { toast('⚠️ PDF reader not loaded.'); return; }
    reader.onload = async function() {
      try {
        const pdf = await pdfjsLib.getDocument(new Uint8Array(this.result)).promise;
        let text = '';
        for (let i = 1; i <= pdf.numPages; i++) {
          const page = await pdf.getPage(i);
          const tc = await page.getTextContent();
          text += tc.items.map(s => s.str).join(' ') + '\n\n';
        }

        const range = quill.getSelection(true);
        const idx = range ? range.index : 0;

        // Insert link placeholder for the PDF
        const fileLink = makeFileLinkPlaceholder(file);
        quill.clipboard.dangerouslyPasteHTML(idx, fileLink);
        moveCursorAfterLastFileLink();

        // Upload the file so it can be opened later (instead of relying on blob URLs)
        uploadAttachedFile(file, (url) => {
          const link = Array.from(document.querySelectorAll('.file-link-placeholder')).find(el => el.dataset.fileName === file.name);
          if (link) {
            link.dataset.href = url;
            link.href = url;
          }
        });

        // Optionally insert extracted text after the link
        if (text.trim()) {
          const range2 = quill.getSelection(true);
          quill.clipboard.dangerouslyPasteHTML(range2 ? range2.index : 0, text.trim().replace(/\n\n/g, '<p><br></p>').replace(/\n/g, '<br>'));
        }

        toast('📄 PDF inserted with link!');
      } catch(err) { console.error(err); }
    };
    reader.readAsArrayBuffer(file);
  } else if (ext === 'docx' || ext === 'doc') {
    setTitleFromFileName(file.name);
    const range = quill.getSelection(true);
    const idx = range ? range.index : 0;
    const fileLink = makeFileLinkPlaceholder(file);
    quill.clipboard.dangerouslyPasteHTML(idx, fileLink);
    moveCursorAfterLastFileLink();

    // Upload so the file is accessible later (and so docs open properly, not as blank)
    uploadAttachedFile(file, (url) => {
      const link = Array.from(document.querySelectorAll('.file-link-placeholder')).find(el => el.dataset.fileName === file.name);
      if (link) {
        link.dataset.href = url;
        link.href = url;
      }
    });

    if (typeof mammoth !== 'undefined') {
      reader.onload = ev => {
        mammoth.convertToHtml({ arrayBuffer: ev.target.result })
          .then(r => {
            if (r.value.trim()) {
              const range2 = quill.getSelection(true);
              quill.clipboard.dangerouslyPasteHTML(range2 ? range2.index : 0, r.value);
              toast('📝 Document extracted at cursor!');
            }
          });
      };
      reader.readAsArrayBuffer(file);
    } else {
      toast('📝 Document inserted. Preview not available (mammoth missing).');
    }
  } else if (['mp3','wav','ogg','m4a'].includes(ext)) {
    toast('🎵 Audio attached! (Will be available after publishing)');
  } else if (['mp4','webm','mov'].includes(ext)) {
    toast('🎬 Video attached! (Will be available after publishing)');
  } else {
    toast('✅ File attached!');
  }
});

// ── Form submit ───────────────────────────────────────────────
let lastNonEmptyContent = quill.root.innerHTML;
quill.on('text-change', () => {
  const current = quill.root.innerHTML;
  if (current && current.trim() && current.trim() !== '<p><br></p>') {
    lastNonEmptyContent = current;
  }
});

document.getElementById('resourceForm').onsubmit = function() {
  if (pendingUploads > 0) {
    toast('⏳ Still uploading attached files. Please wait a moment.');
    return false;
  }

  const current = quill.root.innerHTML;
  document.getElementById('contentInput').value = (current && current.trim() && current.trim() !== '<p><br></p>') ? current : lastNonEmptyContent;
};

// ── Toast ─────────────────────────────────────────────────────
// Keep toolbar visible while scrolling (floats when its original slot is off-screen)
(function() {
  const toolbar = document.getElementById('composerToolbar') || document.querySelector('.composer-toolbar');
  if (!toolbar || !toolbar.parentNode) return;

  const sentinel = document.createElement('div');
  sentinel.className = 'composer-toolbar-sentinel';
  sentinel.setAttribute('aria-hidden', 'true');
  sentinel.style.height = '1px';
  sentinel.style.width = '100%';
  sentinel.style.pointerEvents = 'none';
  toolbar.parentNode.insertBefore(sentinel, toolbar);

  function isInViewport(el) {
    const r = el.getBoundingClientRect();
    const vh = window.innerHeight || document.documentElement.clientHeight || 0;
    return r.bottom > 0 && r.top < vh;
  }

  function setFloating(shouldFloat) {
    toolbar.classList.toggle('is-floating', !!shouldFloat);
  }

  // Set initial state to avoid a flash of the toolbar in the wrong position
  setFloating(!isInViewport(sentinel));

  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      const entry = entries && entries[0];
      if (!entry) return;
      setFloating(!entry.isIntersecting);
    }, { root: null, threshold: 0 });
    io.observe(sentinel);
  } else {
    const onScroll = () => setFloating(!isInViewport(sentinel));
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    onScroll();
  }
})();

function toast(msg) {
  const t = document.getElementById('dash-toast');
  t.textContent = msg; t.style.opacity = '1';
  clearTimeout(t._t); t._t = setTimeout(() => t.style.opacity = '0', 3000);
}
</script>
@endpush
@endsection
