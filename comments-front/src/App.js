import someImage from './city.jpg';
import React, { useEffect, useRef } from 'react';
import './App.css';
import NewComment from './components/NewComment';
import CommentElement from './components/CommentElement';
import { useDispatch, useSelector } from 'react-redux';
import { getComments, addComment, actions } from './redux/comments_reducer';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import * as ScrollArea from '@radix-ui/react-scroll-area';
import ReCAPTCHA from 'react-google-recaptcha';

/*
    Компонента, которая отображает сохраненное изображение,
    форму для создания комментариев к нему и все комментарии, имеющиеся в базе данных
*/

function App() {
  const dispatch = useDispatch();
  const commentsData = useSelector((state) => state.comments.commentsData); // массив данных обо всех комментариях
  const errorData = useSelector((state) => state.comments.error); // массив данных об ошибках, поступающих с сервера

  // Запрос на сервер для обновления массива данных о комментариях
  useEffect(() => {
    dispatch(getComments());
  }, [dispatch]);

  // ReCaptcha
  const secret = '6LfQ8BAoAAAAAAWx3lLEx7__Pt5dgcwzzjvmmcLD';
  const captchaRef = useRef(null);

  // Обработчик ошибки. При возникновении ошибки всплывает информационное окно
  const customId = 'custom-id-yes';
  const notify = () => {
    let info = 'Что-то пошло не так. Попробуйте позже.';
    if (errorData.details && errorData.details.length !== 0) {
      info = errorData.details;
    }
    const text = `${errorData.errCode}: ${info}`;
    toast.error(text, {
      position: toast.POSITION.TOP_RIGHT,
      toastId: customId,
    });
    dispatch(actions.clearErr());
  };
  if (errorData.errCode) {
    notify();
  }

  // Преобразование массива данных для упорядочивания комментариев по дате добавления
  const reversedData = commentsData?.sort((a, b) => {
    const dateA = a.creationDate;
    const dateB = b.creationDate;
    if (dateA < dateB) {
      return 1;
    }
    if (dateA > dateB) {
      return -1;
    }
  });

  // //
  // let allComments = reversedData?.map((com) => (
  //   <CommentElement
  //     key={com.id}
  //     id={com.id}
  //     name={com.userName}
  //     comment={com.comment}
  //     date={com.creationDate} />
  // ));

  // Обработчик добавления нового комментария с Recaptcha
  const addPost = async (name, comment) => {
    let token = '';
    try {
      token = await captchaRef.current.executeAsync();
      captchaRef.current.reset();
    } catch (err) {
      console.log('ERROR captcha');
    }

    const data = new Date(); // автоматическое определение даты и времени создания комментария
    const body = {
      userName: name,
      comment: comment,
      creationDate: data,
      captcha: token,
    };
    dispatch(addComment(body));
  };

  return (
    <ScrollArea.Root className='ScrollAreaRoot'>
      <ScrollArea.Viewport className='ScrollAreaViewport'>
        <div className='container'>
          <ToastContainer autoClose={3000} theme='colored' />
          <div className='image_container'>
            <img alt='image' src={someImage} />
          </div>
          <NewComment onAddComment={addPost} />
          <ReCAPTCHA ref={captchaRef} sitekey={secret} size='invisible' />
          <div className='comments'>
            {reversedData?.map((com) => (
              <CommentElement
                key={com.id}
                id={com.id}
                name={com.userName}
                comment={com.comment}
                date={com.creationDate}
              />
            ))}
          </div>
        </div>
      </ScrollArea.Viewport>
      <ScrollArea.Scrollbar className='ScrollAreaScrollbar' orientation='vertical'>
        <ScrollArea.Thumb className='ScrollAreaThumb' />
      </ScrollArea.Scrollbar>
      <ScrollArea.Scrollbar className='ScrollAreaScrollbar' orientation='horizontal'>
        <ScrollArea.Thumb className='ScrollAreaThumb' />
      </ScrollArea.Scrollbar>
      <ScrollArea.Corner className='ScrollAreaCorner' />
    </ScrollArea.Root>
  );
}

export default App;
