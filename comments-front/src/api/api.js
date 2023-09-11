// eslint-disable-next-line no-unused-vars
import axios, * as others from 'axios';

const instance = axios.create({
  baseURL: 'http://localhost:8888',
});

export const commentsAPI = {
  async getComments() {
    // запрос данных обо всех комментариях
    const response = await instance.get('/comments');
    return response.data;
  },
  async postComment(body) {
    // запрос на сохранение нового комментария
    const response = await instance.post('/comments', body);
    return response.data;
  },
  async deleteComment(id) {
    // запрос на удаление комментария
    const response = await instance.delete(`/comments/${id}`);
    return response.data;
  },
};
