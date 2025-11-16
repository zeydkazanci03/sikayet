{{-- Search Form Komponenti --}}
@props(['placeholder' => 'Ara...', 'name' => 'search', 'value' => ''])

<form action="{{ url()->current() }}" method="GET" class="d-flex">
    <div class="input-group">
        <input type="text"
               name="{{ $name }}"
               class="form-control"
               placeholder="{{ $placeholder }}"
               value="{{ request($name, $value) }}"
               aria-label="{{ $placeholder }}">
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-search"></i> Ara
        </button>
    </div>
</form>
