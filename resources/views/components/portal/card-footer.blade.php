{{--
    Footer standar buat 5 app-card di dashboard ("Lihat Detail" + status dot).
    Statis (gak ada data dinamis), jadi gak perlu @props — tinggal <x-portal.card-footer />
--}}
<div class="card-footer">
    <span class="card-open-btn">
        Lihat Detail
        <x-icon name="chevron-right" width="14" height="14" stroke-width="2.5" />
    </span>
    <span class="card-status-dot"></span>
</div>