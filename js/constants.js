class Constants {
    weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    
    getTextColorByDay(_day){
        switch (_day){
            case 0:
                return 'text-danger';
            case 6:
                return 'text-primary';
            default:
                return '';
        }
    }
}

const _constants = new Constants();