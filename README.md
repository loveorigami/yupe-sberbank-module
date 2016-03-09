# yupe-sb
Модуль приёма оплаты банковскими картами через сервис Сбербанка для [CMS Yupe!](http://yupe.ru)

## Установка и настройка
* В панели управления сайтом установить модуль в разделе `Юпи - Модули`
* Создать способ оплаты (`Магазин - Оплата`) выбрав Sberbank во вкладке `Платежная система`
* Указать `login`, `url мерчанта` (_выдается при заключении договора_) и `password`
* Нажать кнопку `Добавить способ оплаты и продолжить`
* В случае успешного создания способа оплаты, на экране, помимо указанных вами данных, должна появиться строка `Ссылка для HTTP уведомлений платежной системы`.
Содержимое для примера: `http://yupe.ru/payment/process/3`, где `3` - это `<id способа оплаты>` (в вашем случае может быть другим).
* В панели управления модулем Sberbank-a указать адрес возврата для успешной оплаты и оплаты с ошибкой как: `http://<адрес вашего сайта>/payment/process/<id способа оплаты>`.
Для примера выше это будет `http://yupe.ru/payment/process/3`

## URL для доступа к методам REST (для последующей доработки модуля):
* Регистрация заказа https://3dsec.sberbank.ru/payment/rest/register.do
* Регистрация заказа с предавторизацией https://3dsec.sberbank.ru/payment/rest/registerPreAuth.do
* Запрос завершения оплаты заказа https://3dsec.sberbank.ru/payment/rest/deposit.do
* Запрос отмены оплаты заказа https://3dsec.sberbank.ru/payment/rest/reverse.do
* Запрос возврата средств оплаты заказа https://3dsec.sberbank.ru/payment/rest/refund.do
* Получение статуса заказа https://3dsec.sberbank.ru/payment/rest/getOrderStatus.do
* Получение статуса заказа https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
* Запрос проверки вовлеченности карты в 3DS https://3dsec.sberbank.ru/payment/rest/verifyEnrollment.do
* Запрос проведения оплаты по связкам https://3dsec.sberbank.ru/payment/rest/paymentOrderBinding.do
* Запрос деактивации связки https://3dsec.sberbank.ru/payment/rest/unBindCard.do
* Запрос активации связки https://3dsec.sberbank.ru/payment/rest/bindCard.do
* Запрос изменения срока действия связки https://3dsec.sberbank.ru/payment/rest/extendBinding.do
* Запрос списка возможных связок для мерчанта https://3dsec.sberbank.ru/payment/rest/getBindings.do
* Запрос статистики по платежам за период https://3dsec.sberbank.ru/payment/rest/getLastOrdersForMerchants.do

## Тестовая карточка для успешной оплаты
pan:  4111 1111 1111 1111
exp date: 2019/12
владелец: любой (анг)
cvv2: 123
pass: 12345678 (на след шаге)

## Тестовая карточка для оплаты с ошибкой
pan:   5555 5555 5555 5557
exp date: 2019/12
владелец: любой (анг)
cvv2: 123
pass: 12345678 (на след шаге)
