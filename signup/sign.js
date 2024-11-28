document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('signupForm').addEventListener('submit', async event => {
    event.preventDefault()

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

      const result = await response.json()

      if (!response.ok) {
        throw new Error(result.message || 'Error al crear la cuenta')
      }

      alert(`Resultado: ${result.message}`)
      window.location.href = '../login/'
    } catch (error) {
      document.getElementById('errorMessage').textContent = error.message
      document.getElementById('errorMessage').style.display = 'block'
    }

    localStorage.setItem('user', JSON.stringify(signupdata))
    console.log(localStorage.getItem('user'))
  })
})
