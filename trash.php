<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Thùng rác</title><link rel="stylesheet" href="assets/style.css"></head><body><div id="nav" class="nav"></div><div class="container"><h2>Thùng rác (xoá tạm)</h2><div id="content"></div></div>
<script src="assets/app.js"></script><script>
(async()=>{await requireAuth();load();})();
const apiMap={students:'students',subjects:'subjects',scores:'scores',exam_rooms:'exam_rooms',score_assignments:'score_assignments'};
async function load(){
  const d=await (await fetch('api/trash.php')).json();
  content.innerHTML='';
  Object.keys(apiMap).forEach(type=>{content.innerHTML+=`<h3>${type}</h3><table><tr><th>ID</th><th>Mã</th><th>Tên</th><th>Xoá lúc</th><th>Khôi phục</th><th>Xoá thật</th></tr>${(d[type]||[]).map(x=>`<tr><td>${x.id}</td><td>${x.code}</td><td>${x.name}</td><td>${x.deleted_at||''}</td><td><button onclick="restore('${type}',${x.id})">Khôi phục</button></td><td><button onclick="hardDel('${type}',${x.id})">Xoá thật</button></td></tr>`).join('')}</table>`});
}
async function restore(type,id){await fetch('api/'+apiMap[type]+'.php',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});load();}
async function hardDel(type,id){await fetch('api/'+apiMap[type]+'.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,hard:1})});load();}
</script></body></html>
