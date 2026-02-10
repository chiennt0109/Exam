async function getSession(){
  const r = await fetch('api/auth.php?action=session');
  if(!r.ok) return null;
  return r.json();
}

async function requireAuth(){
  const data = await getSession();
  if(!data.user){window.location='index.php'; return null;}
  renderNav(data.user, data.permissions || []);
  return data;
}

function renderNav(user, permissions){
  const nav = document.getElementById('nav');
  if(!nav) return;
  const can = (perm) => user.role==='admin' || permissions.includes(perm);
  nav.innerHTML = `
    <a href='dashboard.php'>Dashboard</a>
    ${can('manage_students') ? "<a href='students.php'>Học sinh</a>" : ''}
    ${can('manage_subjects') ? "<a href='subjects.php'>Môn học</a>" : ''}
    ${can('manage_scores') ? "<a href='scores.php'>Điểm thi</a>" : ''}
    ${can('manage_exam_rooms') ? "<a href='exam_rooms.php'>Phân phòng thi & Báo cáo</a>" : ''}
    ${can('manage_users') ? "<a href='users.php'>Tài khoản & Phân quyền</a>" : ''}
    <a href='trash.php'>Thùng rác</a>
    <span class='right'>${user.full_name} (${user.role}) <button onclick='logout()'>Đăng xuất</button></span>`;
}

async function logout(){
  await fetch('api/auth.php?action=logout',{method:'POST'});
  window.location='index.php';
}
