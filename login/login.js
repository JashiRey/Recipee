document.getElementById('loginForm').addEventListener('submit', async event => {
  event.preventDefault()

  const logindata = {
    email: document.getElementById('email').value,
    password: document.getElementById('password').value
  }

  const response = await fetch('/api/users/auth/index.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(logindata)
  })
  const result = await response.json()
  alert(`Resultado: ${result.message}`)
})
