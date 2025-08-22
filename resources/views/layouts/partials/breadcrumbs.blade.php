<?php
/**
 * @var array $items
 * items: [['label' => 'Dashboard', 'url' => route('dashboard')], ...]
 */
?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb bg-transparent px-0">
    @foreach($items as $i => $item)
      @if(isset($item['url']) && $i < count($items)-1)
        <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
      @else
        <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
      @endif
    @endforeach
  </ol>
</nav>
