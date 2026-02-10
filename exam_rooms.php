<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Phân phòng thi</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div id="nav" class="nav"></div>
<div class="container">
  <h2>Quản lý thi: phân phòng + in danh sách + báo cáo</h2>
  <div>
    <input id="ma_ky_thi" placeholder="Mã kỳ thi" value="THPT2026">
    <input id="room_name" placeholder="Phòng thi (VD: P101)">
    <input id="ma_hs" placeholder="Mã học sinh">
    <button onclick="saveRoom()">Thêm phân phòng</button>
  </div>
  <div>
    <input id="q" placeholder="Tìm kiếm theo phòng/mã HS">
    <button onclick="loadRooms()">Tìm</button>
    <button onclick="printList()">In danh sách thí sinh</button>
    <button onclick="loadReport()">Xem báo cáo</button>
  </div>
  <table id="tb"></table>
  <h3>Danh sách học sinh có sẵn trong CSDL</h3>
  <table id="students"></table>
  <h3>Báo cáo nhanh</h3>
  <div id="report"></div>
</div>
<script src="assets/app.js"></script>
<script>
(async()=>{await requireAuth(); loadRooms(); loadStudents();})();
async function loadRooms(){
  const d=await (await fetch('api/exam_rooms.php?ma_ky_thi='+encodeURIComponent(ma_ky_thi.value)+'&q='+encodeURIComponent(q.value||''))).json();
  tb.innerHTML='<tr><th>Kỳ thi</th><th>Phòng</th><th>Mã HS</th><th>Họ tên</th><th>Lớp</th><th></th></tr>'+
    d.map(x=>`<tr><td>${x.ma_ky_thi}</td><td contenteditable onblur="upd(${x.id})" data-f='room_name'>${x.room_name}</td><td contenteditable onblur="upd(${x.id})" data-f='ma_hs'>${x.ma_hs}</td><td>${x.ho_ten||''}</td><td>${x.ma_lop||''}</td><td><button onclick='delRoom(${x.id})'>Xoá tạm</button></td></tr>`).join('');
}
async function saveRoom(){
  await fetch('api/exam_rooms.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({ma_ky_thi:ma_ky_thi.value,room_name:room_name.value,ma_hs:ma_hs.value})});
  loadRooms();
}
async function upd(id){
  const row=[...tb.querySelectorAll('tr')].find(r=>r.querySelector('button')?.getAttribute('onclick')===`delRoom(${id})`);
  const tds=row.querySelectorAll('td');
  await fetch('api/exam_rooms.php',{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,ma_ky_thi:tds[0].innerText,room_name:tds[1].innerText,ma_hs:tds[2].innerText})});
}
async function delRoom(id){
  await fetch('api/exam_rooms.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
  loadRooms();
}
async function loadStudents(){
  const d=await (await fetch('api/exam_rooms.php?resource=students')).json();
  students.innerHTML='<tr><th>Mã HS</th><th>Họ tên</th><th>Lớp</th><th>Gán nhanh</th></tr>'+
    d.map(x=>`<tr><td>${x.ma_hs}</td><td>${x.ho_ten}</td><td>${x.ma_lop||''}</td><td><button onclick="quickAssign('${x.ma_hs}')">Gán</button></td></tr>`).join('');
}
function quickAssign(code){ma_hs.value=code;}
function printList(){window.print();}
async function loadReport(){
  const d=await (await fetch('api/exam_rooms.php?resource=report&ma_ky_thi='+encodeURIComponent(ma_ky_thi.value))).json();
  report.innerHTML='<b>Theo phòng</b><ul>'+(d.rooms||[]).map(x=>`<li>${x.room_name}: ${x.total} thí sinh</li>`).join('')+'</ul>'+
    '<b>Theo môn điểm</b><ul>'+(d.subjects||[]).map(x=>`<li>${x.ma_mon}: ${x.total} bản ghi điểm</li>`).join('')+'</ul>';
}
</script></body></html>
