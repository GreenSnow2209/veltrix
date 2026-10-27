<div class="vx-card">
    <div class="vx-card-header">
        <h3 class="vx-card-title">{{ $isEditing ? 'Edit Record' : 'Create Record' }}</h3>
        <a href="{{ dirname($action) }}" class="vx-btn vx-btn-subtle vx-btn-sm">&larr; Back to List</a>
    </div>

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="vx-form">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="vx-card-body">
            @if(isset($errors) && $errors->any())
                <div class="vx-toast vx-toast-error" style="margin-bottom: 20px;">
                    <ul style="padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @foreach($fields as $field)
                {!! $field->render() !!}
            @endforeach
        </div>

        <div class="vx-card-footer" style="display: flex; gap: 10px;">
            <button type="submit" class="vx-btn vx-btn-primary">
                {{ $isEditing ? 'Save Changes' : 'Create Record' }}
            </button>
            <button type="reset" class="vx-btn vx-btn-subtle">
                Reset
            </button>
        </div>
    </form>
</div>
