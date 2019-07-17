<nav aria-label="navi">
  <ul class="pagination justify-content-between">
    @if(isset($ret['prev']))
    <li><a class="btn page-link badge-pill" href="{{ url($ret['prev']) }}"><i
          class="fas fa-chevron-left"></i>{{ $ret['prev'] }}</a></li>
    @else
    <li><span class="btn badge-pill disabled"><i class="fas fa-chevron-left"></i> 前のページ</span></li>
    @endif
    <li class="mx-2"><a class="btn page-link badge-pill" href="{{ url($ret['home']) }}">{{ $ret['home'] }}</a></li>
    @if(isset($ret['next']))
    <li><a class="btn page-link badge-pill" href="{{ url($ret['next']) }}">{{ $ret['next'] }} <i
          class="fas fa-chevron-right"></i></a></li>
    @else
    <li><span class="btn badge-pill disabled">次のページ <i class="fas fa-chevron-right"></i></span></li>
    @endif
  </ul>
</nav>