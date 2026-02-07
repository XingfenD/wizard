@php
    // TODO 这个组件好像还没用上, 暂时先不改这里的id
    $subItems = subDocuments($pageItem->id);
@endphp
@if(count($subItems) > 0)
    <div class="list-group">
    @foreach ($subItems as $item)
        <a class="list-group-item" href="{{ wzRoute('project:home', ['id' => $project->id, 'p' => $item->id]) }}">
            {{ $item->title }}
        </a>
    @endforeach
    </div>
@endif