/*LANDING PAGE*/
document.addEventListener('DOMcontentloaded', () => {
  const settingsButton = document.getElementById('toggle-settings')
  const settingsDiv = document.querySelector('.settings')

  settingsButton.addEventListener('click', () => {
    settingsDiv.classList.toggle('active') // Alterna la clase "active"
  })
})
