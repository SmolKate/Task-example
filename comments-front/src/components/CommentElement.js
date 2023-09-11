import React from 'react';
import './../App.css';
import { useDispatch } from 'react-redux';
import { deleteComment } from './../redux/comments_reducer';

/*
    Компонента для отрисовки комментария
*/

const CommentElement = ({ id, name, comment, date }) => {
  const dispatch = useDispatch();

  // Преобразование даных о дате и времени в нужный формат
  date = new Date(date);
  const creationDate = date.toLocaleString('ru-RU');

  // Обработчик удаления комментария
  const deleteHandler = () => {
    dispatch(deleteComment(id));
  };

  return (
    <div className='comment_container'>
      <div className='user_name'>{name}</div>
      <div className='comment_text'>{comment}</div>
      <div className='comment_date'>{creationDate}</div>
      <button onClick={deleteHandler}>Удалить</button>
    </div>
  );
};

export default CommentElement;
