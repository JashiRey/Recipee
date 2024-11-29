const login = localStorage.getItem('user')

if (login) {
  window.location.href = '/home'
}

document.getElementById('loginForm').addEventListener('submit', async event => {
  event.preventDefault()

  const password = document.getElementById('password').value
  const email = document.getElementById('email').value

  const response = await fetch('/api/users/auth/', {
    method: 'POST',
    body: JSON.stringify({ email, password })
  })

  if (response.ok) {
    const { user } = await response.json()
    localStorage.setItem('user', JSON.stringify(user))
    window.location.href = '/home'
  } else {
    alert('Correo electrónico o contraseña incorrecto')
  }
})
