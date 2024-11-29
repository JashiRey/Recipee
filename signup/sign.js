document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('signupForm').addEventListener('submit', async event => {
    event.preventDefault()

    document.getElementById('errorMessage').textContent = ''
    document.getElementById('errorMessage').style.display = 'none'

    const signupdata = {
      name: document.getElementById('username').value,
      email: document.getElementById('email').value,
      password: document.getElementById('password').value
    }

    try {
      const response = await fetch('/api/users/index.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(signupdata)
      })

      if (!response.ok) {
        throw new Error((await response.text()) || 'Error al crear la cuenta')
      }

      localStorage.setItem('user', JSON.stringify({ email: signupdata.email, name: signupdata.name }))

      window.location.href = '/home'
    } catch (error) {
      let message
      try {
        message = message.message
      } catch (e) {
        message = error
      }
      document.getElementById('errorMessage').textContent = message
      document.getElementById('errorMessage').style.display = 'block'
    }
  })
})
