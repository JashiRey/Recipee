// Base API configuration
const API_BASE_URL = '/api'

// API response handler
const handleResponse = async response => {
  const data = await response.json()
  if (!response.ok) {
    throw new Error(data.message || 'API request failed')
  }
  return data
}

// API request configuration
const apiRequest = async (endpoint, options = {}) => {
  const defaultOptions = {
    headers: {
      'Content-Type': 'application/json'
    }
  }

  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, { ...defaultOptions, ...options })
    return handleResponse(response)
  } catch (error) {
    console.error('API Request failed:', error)
    throw error
  }
}

// Users API
const UsersAPI = {
  // Get all users or filter by id/email
  getUsers: (params = {}) => {
    const queryString = new URLSearchParams(params).toString()
    return apiRequest(`/users/${queryString ? '?' + queryString : ''}`)
  },

  // Create new user
  createUser: userData => {
    return apiRequest('/users/', {
      method: 'POST',
      body: JSON.stringify(userData)
    })
  },

  // Update user
  updateUser: userData => {
    return apiRequest('/users/', {
      method: 'PUT',
      body: JSON.stringify(userData)
    })
  },

  // Delete user
  deleteUser: userId => {
    return apiRequest(`/users/?id=${userId}`, {
      method: 'DELETE'
    })
  },

  // Authenticate user
  authenticate: credentials => {
    return apiRequest('/users/auth/', {
      method: 'POST',
      body: JSON.stringify(credentials)
    })
  }
}

// Recipes API
const RecipesAPI = {
  // Get all recipes or filter by params
  getRecipes: (params = {}) => {
    const queryString = new URLSearchParams(params).toString()
    return apiRequest(`/recipes/${queryString ? '?' + queryString : ''}`)
  },

  // Create new recipe
  createRecipe: recipeData => {
    return apiRequest('/recipes/', {
      method: 'POST',
      body: JSON.stringify(recipeData)
    })
  },

  // Update recipe
  updateRecipe: recipeData => {
    return apiRequest('/recipes/', {
      method: 'PUT',
      body: JSON.stringify(recipeData)
    })
  },

  // Delete recipe
  deleteRecipe: recipeId => {
    return apiRequest(`/recipes/?id=${recipeId}`, {
      method: 'DELETE'
    })
  }
}

// Ingredients API
const IngredientsAPI = {
  // Get all ingredients or filter by params
  getIngredients: (params = {}) => {
    const queryString = new URLSearchParams(params).toString()
    return apiRequest(`/ingredients/${queryString ? '?' + queryString : ''}`)
  },

  // Create new ingredient
  createIngredient: ingredientData => {
    return apiRequest('/ingredients/', {
      method: 'POST',
      body: JSON.stringify(ingredientData)
    })
  },

  // Update ingredient
  updateIngredient: ingredientData => {
    return apiRequest('/ingredients/', {
      method: 'PUT',
      body: JSON.stringify(ingredientData)
    })
  },

  // Delete ingredient
  deleteIngredient: ingredientId => {
    return apiRequest(`/ingredients/?id=${ingredientId}`, {
      method: 'DELETE'
    })
  }
}

// Baskets API
const BasketsAPI = {
  // Key for localStorage
  STORAGE_KEY: 'recipee_baskets',

  // Get all baskets
  getBaskets: () => {
    const baskets = localStorage.getItem(BasketsAPI.STORAGE_KEY)
    return baskets ? JSON.parse(baskets) : []
  },

  // Get single basket by ID
  getBasket: basketId => {
    const baskets = BasketsAPI.getBaskets()
    return baskets.find(basket => basket.id === basketId)
  },

  // Create new basket
  createBasket: ingredients => {
    const baskets = BasketsAPI.getBaskets()

    // Generate new ID (max existing ID + 1, or 1 if no baskets exist)
    const newId = baskets.length > 0 ? Math.max(...baskets.map(b => b.id)) + 1 : 1

    // Create new basket
    const newBasket = {
      id: newId,
      ingredients: ingredients.map(ing => ({
        id: ing.id,
        name: ing.name
      }))
    }

    // Save to localStorage
    localStorage.setItem(BasketsAPI.STORAGE_KEY, JSON.stringify([...baskets, newBasket]))

    return newBasket
  },

  // Update basket
  updateBasket: (basketId, ingredients) => {
    const baskets = BasketsAPI.getBaskets()
    const index = baskets.findIndex(basket => basket.id === basketId)
    if (index !== -1) {
      baskets[index].ingredients = ingredients.map(ing => ({
        id: ing.id,
        name: ing.name
      }))
      localStorage.setItem(BasketsAPI.STORAGE_KEY, JSON.stringify(baskets))
    }
  }
}

// Export all APIs
export { UsersAPI, RecipesAPI, IngredientsAPI, BasketsAPI }
