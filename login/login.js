document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('loginForm').addEventListener('submit', async event => {
    event.preventDefault()

    password = document.getElementById('password').value

    var login = localStorage.getItem(user)

    if (login) {
      var parsedUser = JSON.parse(login)
      if (parsedUser.password === password) {
        localStorage.setItem('user', JSON.stringify(parsedUser))
        alert('Bienvenido')
        console.log(localStorage(login))
      } else {
        if (parsedUser.password !== password) {
          alert('Contraseña incorrecta')
        } else {
          alert('Correo electrónico incorrecto')
        }
      }
    } else {
      alert('Email no encontrado, porfavor registrate')
    }
  })
})
