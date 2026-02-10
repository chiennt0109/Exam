async function getSession(){
  const r=await fetch('api/auth.php?action=session');
  if(!r.ok) return null;
  return r.json();
}

async function requireAuth(){
  const data=await getSession();
  if(!data.user){window.location='index.php'; return null;}
  renderNav(data.user);
  return data;
}

function renderNav(user){
  const nav=document.getElementById('nav');
  if(!nav) return;
  nav.innerHTML=`
    <a href='dashboard.php'>Dashboard</a>
    <a href='students.php'>Học sinh</a>
    <a href='subjects.php'>Môn học</a>
    <a href='scores.php'>Điểm thi</a>
    ${user.role==='admin' ? "<a href='users.php'>Tài khoản & Phân quyền</a>" : ''}
    <a href='trash.php'>Thùng rác</a>
    <span class='right'>${user.full_name} (${user.role}) <button onclick='logout()'>Đăng xuất</button></span>`;
}

async function logout(){await fetch('api/auth.php?action=logout',{method:'POST'});window.location='index.php';}
