import { UsersAPI } from '/recipee/js/api/index.js'

window.handleSignup = async event => {
  event.preventDefault()

  const username = document.getElementById('username').value
  const email = document.getElementById('email').value
  const password = document.getElementById('password').value
  const errorMessage = document.getElementById('errorMessage')
  const signupButton = document.querySelector('.login-button')
  const form = document.getElementById('signupForm')

  errorMessage.style.display = 'none'

  try {
    signupButton.disabled = true
    signupButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando cuenta...'

    const response = await UsersAPI.createUser({
      name: username,
      email,
      password
    })

    // Auto login after signup
    const loginResponse = await UsersAPI.authenticate({
      email,
      password
    })

    localStorage.setItem('user', JSON.stringify(loginResponse.user))
    window.location.href = '/home/'
  } catch (error) {
    errorMessage.textContent = error.message || 'Error al crear la cuenta'
    errorMessage.style.display = 'block'
    form.classList.add('shake')

    setTimeout(() => {
      form.classList.remove('shake')
    }, 400)
  } finally {
    signupButton.disabled = false
    signupButton.innerHTML = 'Crear Cuenta'
  }
}
