import { Form, Field } from 'formik';
import React from 'react';
import './../App.css';

/*
    Форма для добавления нового комментария
*/

const NewCommentForm = ({ errors, touched, status }) => {
  return (
    <Form className='form'>
      <div>{!!status && <div className='errorMsg'>{status}</div>}</div>
      <div className={touched.name && errors.name ? 'errorMsgCont' : ''}>
        <Field name='name' type='text' placeholder='Имя' />
        {touched.name && errors.name && <div className='errorMsg'>{errors.name}</div>}
      </div>
      <div className={touched.comment && errors.comment ? 'errorMsgCont' : ''}>
        <Field name='comment' type='text' as='textarea' placeholder='Текст комментария' />
        {touched.comment && errors.comment && <div className='errorMsg'>{errors.comment}</div>}
      </div>
      <div className={errors.comment || errors.name ? 'errorMsgBtn' : ''}>
        <button type='submit'>Добавить комментарий</button>
      </div>
    </Form>
  );
};

export default NewCommentForm;
