<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Điểm thi</title><link rel="stylesheet" href="assets/style.css"></head><body><div id="nav" class="nav"></div><div class="container"><h2>Quản lý điểm thi</h2>
<input id="ma_ky_thi" placeholder="Mã kỳ thi"><input id="ma_hs" placeholder="Mã HS"><input id="sbd" placeholder="SBD"><input id="ma_mon" placeholder="Mã môn"><input id="component" placeholder="Thành phần điểm" value="Tong"><input id="diem" placeholder="Điểm"><button onclick="save()">Thêm</button>
<input id="q" placeholder="Tìm kiếm"><button onclick="load()">Tìm</button><table id="tb"></table>
<div id="my_assignments"></div></div>
<script src="assets/app.js"></script><script>
(async()=>{const s=await requireAuth();if(!s)return;load();if(s.user.role==='score_input')loadMine();})();
async function load(){const d=await (await fetch('api/scores.php?q='+encodeURIComponent(q.value||''))).json();tb.innerHTML='<tr><th>Kỳ thi</th><th>HS</th><th>SBD</th><th>Môn</th><th>TP điểm</th><th>Điểm</th><th></th></tr>'+d.map(x=>`<tr><td>${x.ma_ky_thi}</td><td>${x.ma_hs}</td><td>${x.sbd||''}</td><td>${x.ma_mon}</td><td>${x.component}</td><td>${x.diem}</td><td><button onclick='delS(${x.id})'>Xoá tạm</button></td></tr>`).join('');}
async function save(){const r=await fetch('api/scores.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({ma_ky_thi:ma_ky_thi.value,ma_hs:ma_hs.value,sbd:sbd.value,ma_mon:ma_mon.value,component:component.value,diem:diem.value})});const d=await r.json();if(!r.ok){alert(d.error||'Lỗi');return;}load();}
async function delS(id){await fetch('api/scores.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});load();}
async function loadMine(){const d=await (await fetch('api/scores.php?resource=my_assignments')).json();my_assignments.innerHTML='<h3>Phân công nhập điểm của bạn</h3><ul>'+d.map(x=>`<li>${x.ma_mon} - ${x.component}</li>`).join('')+'</ul>'}
</script></body></html>
