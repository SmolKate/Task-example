import { commentsAPI } from '../api/api';

export const actions = {
  getCommentsAC: (comments) => ({ type: 'comments/GET_COMMENTS', comments }),
  setError: (errCode, details) => ({ type: 'comments/SET_ERROR', data: { errCode, details } }),
  clearErr: () => ({ type: 'comments/CLEAR_ERROR' }),
};

// Обработчик ошибок с сервера, который преобразует их и сохраняет в state
const errorHandler = (err) => (dispatch) => {
  let details = null;
  let status = null;
  if (err.response?.data?.error) {
    details = err.response.data.error;
  }
  if (err.response) {
    status = err.response.status;
  } else {
    status = 'Ошибка';
    details = err.message;
  }
  dispatch(actions.setError(status, details));
};

let initialState = {
  commentsData: null,
  error: {
    errCode: null,
    details: null,
  },
};

const commentsReducer = (state = initialState, action) => {
  switch (action.type) {
    case 'comments/GET_COMMENTS':
      return {
        ...state,
        commentsData: action.comments,
      };
    case 'comments/SET_ERROR':
      return {
        ...state,
        error: action.data,
      };
    case 'comments/CLEAR_ERROR':
      return {
        ...state,
        error: {
          ...state.error,
          errCode: null,
          details: null,
        },
      };
    default:
      return state;
  }
};

export default commentsReducer;

// Получение данных о комментариях и сохранение их в state
export const getComments = () => async (dispatch) => {
  try {
    const data = await commentsAPI.getComments();
    dispatch(actions.getCommentsAC(data));
  } catch (err) {
    dispatch(errorHandler(err));
  }
};

// Добавление нового комментария и обновление данных о комментариях в state
export const addComment = (body) => async (dispatch) => {
  try {
    await commentsAPI.postComment(body);
    dispatch(getComments());
  } catch (err) {
    dispatch(errorHandler(err));
  }
};

// Удаление комментария и обновление данных о комментариях в state
export const deleteComment = (id) => async (dispatch) => {
  try {
    await commentsAPI.deleteComment(id);
    dispatch(getComments());
  } catch (err) {
    dispatch(errorHandler(err));
  }
};
