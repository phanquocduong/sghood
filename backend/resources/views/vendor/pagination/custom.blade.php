@if ($paginator->hasPages())
         <nav aria-label="Pagination">
             <ul class="pagination">
                 {{-- Previous Page Link --}}
                 @if ($paginator->onFirstPage())
                     <li class="page-item disabled" aria-disabled="true">
                         <span class="page-link">@lang('pagination.previous')</span>
                     </li>
                 @else
                     <li class="page-item">
                         <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a>
                     </li>
                 @endif

                 {{-- Pagination Elements --}}
                 @foreach ($elements as $element)
                     @if (is_string($element))
                         <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                     @endif

                     @if (is_array($element))
                         @foreach ($element as $page => $url)
                             @if ($page == $paginator->currentPage())
                                 <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                             @else
                                 <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                             @endif
                         @endforeach
                     @endif
                 @endforeach

                 {{-- Next Page Link --}}
                 @if ($paginator->hasMorePages())
                     <li class="page-item">
                         <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a>
                     </li>
                 @else
                     <li class="page-item disabled" aria-disabled="true">
                         <span class="page-link">@lang('pagination.next')</span>
                     </li>
                 @endif
             </ul>
         </nav>
     @endif
