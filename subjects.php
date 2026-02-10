<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Môn học</title><link rel="stylesheet" href="assets/style.css"></head><body><div id="nav" class="nav"></div><div class="container"><h2>Quản lý môn học</h2>
<input id="ma_mon" placeholder="Mã môn"><input id="ten_mon" placeholder="Tên môn"><button onclick="save()">Thêm</button>
<input id="q" placeholder="Tìm kiếm"><button onclick="load()">Tìm</button><table id="tb"></table></div>
<script src="assets/app.js"></script><script>
(async()=>{await requireAuth();load();})();
async function load(){const d=await (await fetch('api/subjects.php?q='+encodeURIComponent(q.value||''))).json();tb.innerHTML='<tr><th>Mã</th><th>Tên</th><th></th></tr>'+d.map(x=>`<tr><td>${x.ma_mon}</td><td contenteditable onblur="upd(${x.id},this.innerText)">${x.ten_mon}</td><td><button onclick='delS(${x.id})'>Xoá tạm</button></td></tr>`).join('');}
async function save(){await fetch('api/subjects.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({ma_mon:ma_mon.value,ten_mon:ten_mon.value})});load();}
async function upd(id,ten_mon){await fetch('api/subjects.php',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,ten_mon})});}
async function delS(id){await fetch('api/subjects.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});load();}
</script></body></html>
