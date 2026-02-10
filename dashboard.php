<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Dashboard</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div id="nav" class="nav"></div><div class="container"><h2>Dashboard hệ thống quản lý thi</h2><div class="grid" id="cards"></div></div>
<script src="assets/app.js"></script><script>
(async()=>{const s=await requireAuth(); if(!s) return; const p=s.permissions; const cards=[
['Học sinh','students.php','manage_students'],['Môn học','subjects.php','manage_subjects'],['Điểm thi','scores.php','manage_scores'],['Tài khoản','users.php','manage_users'],['Thùng rác','trash.php','view_dashboard']];
cardsEl=document.getElementById('cards'); cards.forEach(c=>{if(s.user.role==='admin'||p.includes(c[2])) cardsEl.innerHTML+=`<div class='card'><h3>${c[0]}</h3><a href='${c[1]}'>Mở</a></div>`;});})();
</script></body></html>
