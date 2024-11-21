document.getElementById('loginForm').addEventListener('submit', async event => {
  event.preventDefault()

  const logindata = {
    email: document.getElementById('email').value,
    password: document.getElementById('password').value
  }

  try {
    const response = await fetch('/api/users/auth/index.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(logindata)
    })

    const result = await response.json()

    if (!response.ok) {
      throw new Error(result.message || 'Correo o contraseña inválidos')
    }

    alert(`Resultado: ${result.message}`)
  } catch (error) {
    document.getElementById('errorMessage').textContent = error.message
    document.getElementById('errorMessage').style.display = 'block'
  }
})
