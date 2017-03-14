import { combineReducers } from 'redux'
import { ADD_TODO, TOGGLE_TODO, SET_VISIBILITY_FILTER, VisibilityFilters } from './../actions'
const { SHOW_ALL } = VisibilityFilters

/* Define one reducer responsible for handling visibility filter state */
function visibilityFilter(state = SHOW_ALL, action) {
  switch (action.type) {
    case SET_VISIBILITY_FILTER:
      return action.filter
    default:
      return state
  }
}

/* Define another reducer to handle the todo list */
function todos(state = [], action) {
  switch (action.type) {
    case ADD_TODO:
      return [
        ...state,
        {
          text: action.text,
          id: action.id,
          completed: false
        }
      ]
    case TOGGLE_TODO:
      return state.map((todo, index) => {
        if (index === action.index) {
          return Object.assign({}, todo, {
            completed: !todo.completed
          })
        }
        return todo
      })
    default:
      return state
  }
}

/* Combine our two reducers to obtain one main reducer that we will pass to the Redux store */
const todoApp = combineReducers({
  visibilityFilter,
  todos
})

export default todoApp
