document.getElementById('signupForm').addEventListener('submit', async event => {
  event.preventDefault()

  const signupdata = {
    name: document.getElementById('username').value,
    email: document.getElementById('email').value,
    password: document.getElementById('password').value
  }

  const response = await fetch('/api/users/index.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(signupdata)
  })
  const result = await response.json()
  alert(`Resultado: ${result.message}`)
})
