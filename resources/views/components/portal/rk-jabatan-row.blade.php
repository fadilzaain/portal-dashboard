@props([
    'jabatan',
    'kualifikasi' => '-',
    'pns' => 0,
    'pppk' => 0,
    'pppkPw' => 0,
    'nonAsn' => 0,
    'jumlah' => 0,
    'kebutuhan' => 0,
    'keterangan' => 'CUKUP',
    'badgeClass' => 'rk-badge-green',
])
<tr class="rk-row" data-search="{{ strtolower($jabatan) }}">
    <td class="rk-row-jabatan">
        <div class="rk-row-name">{{ $jabatan }}</div>
        @if ($kualifikasi !== '-')
        <div class="rk-row-kualifikasi">{{ $kualifikasi }}</div>
        @endif
    </td>
    <td class="c">{{ $pns }}</td>
    <td class="c">{{ $pppk }}</td>
    <td class="c">{{ $pppkPw }}</td>
    <td class="c">{{ $nonAsn }}</td>
    <td class="c rk-row-jumlah-cell">{{ $jumlah }}</td>
    <td class="c">{{ $kebutuhan }}</td>
    <td class="c"><span class="rk-badge {{ $badgeClass }}">{{ ucfirst(strtolower($keterangan)) }}</span></td>
</tr>