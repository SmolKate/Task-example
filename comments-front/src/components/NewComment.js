import NewCommentForm from './NewCommentForm';
import { withFormik } from 'formik';
import * as Yup from 'yup';

/*
    Контейнер для формы, содержащий схему валидации, обработчик сохранения формы
    и первоначальные значения для формы
*/

const NewComment = withFormik({
  mapPropsToValues({ name, comment }) {
    return {
      name: name || '',
      comment: comment || '',
    };
  },
  validationSchema: Yup.object().shape({
    name: Yup.string()
      .max(20, 'Максимальное количество символов 20.')
      .required('Обязательное поле'),
    comment: Yup.string()
      .max(300, 'Максимальное количество символов 300.')
      .required('Обязательное поле'),
  }),
  handleSubmit(values, { props: { onAddComment }, setSubmitting }) {
    onAddComment(values.name, values.comment);
    values.name = '';
    values.comment = '';
    values.captcha = '';
    setSubmitting(false);
  },
})(NewCommentForm);

export default NewComment;
