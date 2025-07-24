import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import utc from 'dayjs/plugin/utc';
import 'dayjs/locale/vi';

dayjs.extend(relativeTime);
dayjs.extend(utc);
dayjs.locale('vi');

export function formatTimeAgo(time) {
    const parsedTime = dayjs(time);
    return parsedTime.fromNow();
}
