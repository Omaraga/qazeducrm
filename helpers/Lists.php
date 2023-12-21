<?php

namespace app\helpers;

use app\models\mocks\ServicesMock;
use app\models\Organizations;
use app\modules\services\models\SchoolQuery;
use app\modules\services\models\SpecializedQuery;
use app\modules\services\models\TenclassQuery;
use common\models\relations\UserKid;
use ReflectionMethod;
use Yii;

/**
 * Справочники вынесены в статичный класс. Эти справочники не большие, если развер будет увеличиваться, то
 * можно перенести в базу или кеш или еще куда-нибудь
 *
 * @package common\helpers
 */
class Lists
{

    /**
     * No init
     */
    public function __construct()
    {
    }

    /**
     * Возвращаем данные из словаря (используеися в фильтре twig)
     *
     * @example {{ row.role | dict('roles') }}
     *
     * @param $dictName
     * @param $value
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getValueFromDict($dictName, $value)
    {
        $_class = new self();
        $method = new ReflectionMethod($_class, 'get' . $dictName);
        $data = $method->invoke($_class);
        return isset($data[$value]) ? $data[$value] : '';
    }

    public static function getGenders()
    {
        return [
            '1' => Yii::t('main', 'Муж.'),
            '2' => Yii::t('main', 'Жен.')
        ];
    }

    public static function getNationality()
    {
        // TODO pg2.vpn, db_school_krg_20.public.dic_nationality //TODO переделать справочник
        return [
            1 => Yii::t('main', 'Русский/Русская'),
            2 => Yii::t('main', 'Казах/Казашка'),
            100 => Yii::t('main', 'Кореец/Кореянка'),
            101 => Yii::t('main', 'Немец/Немка'),
            103 => Yii::t('main', 'Украинец/Украинка'),
            104 => Yii::t('main', 'Татарин/Татарка'),
            105 => Yii::t('main', 'Киргиз/Киргизка'),
            106 => Yii::t('main', 'Узбек/Узбечка'),
            107 => Yii::t('main', 'Уйгур/Уйгурка'),
            108 => Yii::t('main', 'Туркмен/Туркменка'),
            109 => Yii::t('main', 'Азербайджанин/Азербайджанка'),
            110 => Yii::t('main', 'Чеченец/Чеченка'),
            111 => Yii::t('main', 'Грузин/Грузинка'),
            112 => Yii::t('main', 'Таджик/Таджичка'),
            116 => Yii::t('main', 'Ингуш/Ингушка'),
            120 => Yii::t('main', 'Осетин/Осетинка'),
            121 => Yii::t('main', 'Кистинец/Кистинка'),
            122 => Yii::t('main', 'Грек/Гречанка'),
            124 => Yii::t('main', 'Армянин/Армянка'),
            118 => Yii::t('main', 'Поляк/Полька'),
            114 => Yii::t('main', 'Дунганец/Дунганка'),
            131 => Yii::t('main', 'Чуваш/Чувашка'),
            132 => Yii::t('main', 'Лезгин/Лезгинка'),
            130 => Yii::t('main', 'Еврей/Еврейка'),
            139 => Yii::t('main', 'Латчик,Латчик'),
            3 => Yii::t('main', 'Чех/Чешка'),
            140 => Yii::t('main', 'Араб/Арабка'),
            123 => Yii::t('main', 'Молдован/Молдованка'),
            143 => Yii::t('main', 'Удмурт/Удмуртка'),
            144 => Yii::t('main', 'Венгр/Венгерка'),
            146 => Yii::t('main', 'Латыш/Латышка'),
            147 => Yii::t('main', 'Калмык/Калмычка'),
            148 => Yii::t('main', 'Карел/Карелка'),
            149 => Yii::t('main', 'Хакас/Хакаска'),
            142 => Yii::t('main', 'Каракалпак/Каракалпачка'),
            113 => Yii::t('main', 'Башкир/Башкирка'),
            141 => Yii::t('main', 'Финн/Финка'),
            117 => Yii::t('main', 'Поляк/Полячка'),
            200 => Yii::t('main', 'Не указана,'),
            115 => Yii::t('main', 'Монгол/Монголка'),
            125 => Yii::t('main', 'Мариец/Марийка'),
            127 => Yii::t('main', 'Литовец/Литовка'),
            128 => Yii::t('main', 'Китаец/Китаянка'),
            129 => Yii::t('main', 'Цыган/Цыганка'),
            133 => Yii::t('main', 'Итальянец/Итальянка'),
            134 => Yii::t('main', 'Болгарин/Болгарка'),
            135 => Yii::t('main', 'Эстонец/Эстонка'),
            137 => Yii::t('main', 'Турок/Турчанка'),
            138 => Yii::t('main', 'Аварец/Аварка'),
            145 => Yii::t('main', 'Японец/Японка'),
            126 => Yii::t('main', 'Мордвин/Мордовка'),
            4 => Yii::t('main', 'Белорус/Белорусска'),
            102 => Yii::t('main', 'Украинец/Украинка'),
            150 => Yii::t('main', 'Француз/Француженка'),
            170 => Yii::t('main', 'Афганец/Афганка'),
            171 => Yii::t('main', 'Иранец/Иранка'),
            172 => Yii::t('main', 'Индус/Индуска'),
            177 => Yii::t('main', 'Курд/Курдянка'),
            151 => Yii::t('main', 'Гагауз,Гагауз'),
            178 => Yii::t('main', 'Ассириец/Ассирийка'),
            201 => Yii::t('main', 'Ногаец/Ногайка'),
            202 => Yii::t('main', 'Даргин/Даргинка'),
            203 => Yii::t('main', 'Румын/Румынка'),
            204 => Yii::t('main', 'Кумык/Кумычка'),
        ];
    }



    public static function getCitizenshipStatus()
    {
        return [
            1 => Yii::t('main', 'Гражданин РК'),
            2 => Yii::t('main', 'Иностранец'),
            3 => Yii::t('main', 'Лицо без гражданства'),
            4 => Yii::t('main', 'Беженец'),
            5 => Yii::t('main', 'Лицо, ищущее убежище'),
            6 => Yii::t('main', 'Оралман'),
        ];
    }

    public static function getStudyLang()
    {
        return [
            1 => Yii::t('main', 'Русский язык'),
            2 => Yii::t('main', 'Казахский язык'),
//            3 => \Yii::t('main', 'Английский язык обучения'),
//            5 => \Yii::t('main', 'Турецкий язык обучения')
        ];
    }

    public static function getLanguageList()
    {
        return [
            'ru-RU' => Yii::t('main', 'Русский язык'),
            'kk-KZ' => Yii::t('main', 'Казахский язык'),
        ];
    }

    public static function getOrderLang()
    {
        return [
            1 => Yii::t('main', 'На русском языке'),
            2 => Yii::t('main', 'На казахском языке')
        ];
    }

    public static function getSchoolEducationForm()
    {
        return [
            1 => Yii::t('main', 'ГУ - Государственное учреждение'),
            2 => Yii::t('main', 'КГУ - Коммунальное государственное учреждение'),
            3 => Yii::t('main', 'КГКП - Казенное государственное коммунальное предприятие'),
            4 => Yii::t('main', 'Республиканское государственное учреждение'),
            5 => Yii::t('main', 'ТОО - Товарищество с ограниченной ответственностью'),
            6 => Yii::t('main', 'ИП - Индивидуальный предприниматель'),
            7 => Yii::t('main', 'ЧУ - Частное учреждение'),
        ];
    }

    public static function getRoles()
    {
        return [
            SystemRoles::SUPER => Yii::t('main', 'Системный администратор'),
            OrganizationRoles::ADMIN => Yii::t('main', 'Администратор'),
            OrganizationRoles::DIRECTOR => Yii::t('main', 'Директор'),
            SystemRoles::PARENT => Yii::t('main', 'Родитель'),
            OrganizationRoles::TEACHER => Yii::t('main', 'Преподаватель'),
            OrganizationRoles::NO_ROLE => Yii::t("main", "Без роли"),
        ];
    }

    /**
     * должности из школы
     *
     * @return array
     */
    public static function getRanks()
    {
        return [
            1 => Yii::t('main', 'Директор'),
            3 => Yii::t('main', 'Заместитель директора по научной работе'),
            4 => Yii::t('main', 'Заместитель директора по учебно-воспитательной работе'),
            5 => Yii::t('main', 'Заместитель директора по учебно-производственной работе'),
            42 => Yii::t('main', 'Заместитель директора по инновациям'),
            43 => Yii::t('main', 'Заместитель директора по информатизации'),
            44 => Yii::t('main', 'Психолог'),
            100 => Yii::t('main', 'Зам. директора по воспитательной работе'),
            153 => Yii::t('main', 'Заместитель директора по учебно-методической работе'),
            161 => Yii::t('main', 'Помощник заместителя директора по АХР'),
            163 => Yii::t('main', 'Заместитель директора по профильной работе'),
            164 => Yii::t('main', 'Заместитель директора по научно-методической работе'),
            213 => Yii::t('main', 'Заместитель директора по учебной работе'),
            40 => Yii::t('main', 'Администратор системы'),
            67 => Yii::t('main', 'Секретарь'),
            22 => Yii::t('main', 'Секретарь по учебной части'),
            23 => Yii::t('main', 'Секретарь (делопроизводитель)'),
            52 => Yii::t('main', 'Социальный педагог'),
            68 => Yii::t('main', 'Делопроизводитель'),
        ];
    }

    public static function getResident()
    {
        return [
            1 => Yii::t('main', 'Гражданин РК (Резидент)'),
            2 => Yii::t('main', 'Не гражданин РК (Не резидент)')
        ];
    }

    /*
    protected static $institutionsTypes = null;
    public static function getSchoolTypes()
    {
        if (!self::$institutionsTypes) {
            //self::$institutionsType =
            $client = new \GuzzleHttp\Client();
            $result = $client->request('GET', 'https://pa.bilimal.kz/handbook/institution-type', [
                'per-page' => 200
            ]);
            echo $result->getStatusCode();
            echo $result->getHeader('content-type');
            echo $result->getBody();

            die();
        }

        return [];
    }
    */

    public static function getChildrenSocialCategory()
    {
        return [
            0 => Yii::t('main', 'Выберите категорию'),
            1 => Yii::t('main', 'Для детей из семей, имеющих право на получение государственной адресной социальной помощи'),
            2 => Yii::t('main', 'Для детей из семей, не получающих государственную адресную социальную помощь, в которых среднедушевой доход ниже величины прожиточного минимума'),
            3 => Yii::t('main', 'Для детей - сирот и детей, оставшиеся без попечения родителей, проживающих в семьях'),
            4 => Yii::t('main', 'Для детей из семей, требующих экстренной помощи в результате чрезвычайных ситуаций и иных категории обучающихся и воспитанников, определяемых коллегиальным органом управления организации образования'),
        ];
    }

    public static function getStudentSocialCategory()
    {
        return [
            0 => Yii::t('main', 'Выберите категорию'),
            1 => Yii::t('main', 'Граждане из числа инвалидов I, II групп, инвалидов с детства, детей-инвалидов'),
            2 => Yii::t('main', 'Лица, приравненные по льготам и гарантиям к участникам и инвалидам Великой Отечественной войны'),
            3 => Yii::t('main', 'Граждане из числа сельской молодежи на специальности, определяющие социально-экономическое развитие села'),
            4 => Yii::t('main', 'Лица казахской национальности, не являющиеся гражданами Республики Казахстан'),
            5 => Yii::t('main', 'Дети-сироты и дети, оставшиеся без попечения родителей, а также граждане Республики Казахстан из числа молодежи, потерявшие или оставшиеся без попечения родителей до совершеннолетия'),
            6 => Yii::t('main', 'Граждане Республики Казахстан из числа сельской молодежи, переселяющиеся в регионы, определенные Правительством Республики Казахстан'),
            7 => Yii::t('main', 'Дети из семей, в которых воспитывается четыре и более несовершеннолетних детей'),
            8 => Yii::t('main', 'Дети из числа неполных семей, имеющих данный статус не менее трех лет'),
            9 => Yii::t('main', 'Дети из семей, воспитывающих детей-инвалидов с детства, инвалидов первой и второй групп'),
        ];
    }

    public static function getAccountingTypes()
    {
        return [
            0 => Yii::t('main', 'Не указано'),
            1 => Yii::t('main', 'Организация образования (Заявления подписываются директором школы)'),
            2 => Yii::t('main', 'Канцелярия услугодателя (Заявления подписываются руководителем отдела образования)'),
        ];
    }

    /*
     * Список предметов для выдачи дубликата аттестата о среднем образовании
     *
     * */
    public static function getCertificateSubjects()
    {
        return [
            'algebra_and_boa' => [
                'label' => Yii::t('main', 'Алгебра и начала анализа'),
                'required' => true,
            ],
            'biology' => [
                'label' => Yii::t('main', 'Биология'),
                'required' => true,
            ],
            'geography' => [
                'label' => Yii::t('main', 'География'),
                'required' => true,
            ],
            'geometry' => [
                'label' => Yii::t('main', 'Геометрия'),
                'required' => true,
            ],
            'art' => [
                'label' => Yii::t('main', 'Изобразительное искусство'),
                'required' => false,
            ],
            'foreign_language' => [
                'label' => Yii::t('main', 'Иностранный язык'),
                'required' => true,
            ],
            'informatics' => [
                'label' => Yii::t('main', 'Информатика'),
                'required' => true,
            ],
            'history_of_kazakhstan' => [
                'label' => Yii::t('main', 'История Казахстана'),
                'required' => true,
            ],
            'kazakh_language_and_literature' => [
                'label' => Yii::t('main', 'Казахский язык и литература'),
                'required' => true,
            ],
            'music' => [
                'label' => Yii::t('main', 'Музыка'),
                'required' => false,
            ],
            'basic_military_training' => [
                'label' => Yii::t('main', 'Начальная военная подготовка'),
                'required' => true,
            ],
            'russian_literature' => [
                'label' => Yii::t('main', 'Русская литература (Н)'),
                'required' => true,
            ],
            'russian_language' => [
                'label' => Yii::t('main', 'Русский язык'),
                'required' => true,
            ],
            'self_knowledge' => [
                'label' => Yii::t('main', 'Самопознание'),
                'required' => true,
            ],
            'technology' => [
                'label' => Yii::t('main', 'Технология'),
                'required' => true,
            ],
            'physics' => [
                'label' => Yii::t('main', 'Физика'),
                'required' => true,
            ],
            'physical_education' => [
                'label' => Yii::t('main', 'Физическая культура'),
                'required' => true,
            ],
            'chemistry' => [
                'label' => Yii::t('main', 'Химия'),
                'required' => true,
            ],
        ];
    }

    /*
     * Причины восстановления аттестата о среднем образовании
     *
     * */
    public static function getCertificateReason()
    {
        return [
            1 => Yii::t('main', 'Утеря'),
            2 => Yii::t('main', 'Порча'),
            3 => Yii::t('main', 'Смена ФИО')
        ];
    }

    /*
     * Типы аттестата о среднем образовании
     *
     * */
    public static function getCertificateType()
    {
        return [
            1 => Yii::t('main', 'Аттестат об общем среднем образовании'),
            2 => Yii::t('main', 'Свидетельство об окончании основной школы')
        ];
    }

    public static function getCampList()
    {
        return [
            1 => Yii::t('main', 'Пришкольный'),
            2 => Yii::t('main', 'Загородный')
        ];
    }

    public static function getWeekDays()
    {
        return [
            1 => Yii::t('main', 'Понедельник'),
            2 => Yii::t('main', 'Вторник'),
            3 => Yii::t('main', 'Среда'),
            4 => Yii::t('main', 'Четверг'),
            5 => Yii::t('main', 'Пятница'),
            6 => Yii::t('main', 'Суббота'),
            7 => Yii::t('main', 'Воскресенье')
        ];
    }

    public static function getWeekDaysShort(): array
    {
        return [
            1 => Yii::t('main', 'Пн'),
            2 => Yii::t('main', 'Вт'),
            3 => Yii::t('main', 'Ср'),
            4 => Yii::t('main', 'Чт'),
            5 => Yii::t('main', 'Пт'),
            6 => Yii::t('main', 'Сб'),
            7 => Yii::t('main', 'Вс')
        ];
    }

    public static function getCategories()
    {
        return [
            18 => Yii::t('main', 'дети-сироты, дети, оставшиеся без попечения родителей'),
            19 => Yii::t('main', 'дети с особыми образовательными потребностями, инвалиды и инвалиды с детства, дети-инвалиды'),
            20 => Yii::t('main', 'дети из многодетных семей'),
            21 => Yii::t('main', 'дети, находящиеся в центрах адаптации несовершеннолетних и центрах поддержки детей, находящихся в трудной жизненной ситуации'),
            22 => Yii::t('main', 'дети, проживающие в школах-интернатах общего и санаторного типов, интернатах при школах'),
            23 => Yii::t('main', 'дети, воспитывающиеся и обучающиеся в специализированных интернатных организациях образования для одаренных детей'),
            24 => Yii::t('main', 'воспитанники интернатных организаций'),
            25 => Yii::t('main', 'дети из семей, имеющих право на получение государственной адресной социальной помощи, а также из семей, не получающих государственную адресную социальную помощь, в которых среднедушевой доход ниже величины прожиточного минимума'),
            26 => Yii::t('main', 'дети, которые по состоянию здоровья в течение длительного времени обучаются по программам начального, основного среднего, общего среднего образования на дому или в организациях, оказывающих стационарную помощь, а также восстановительное лечение и медицинскую реабилитацию'),
            27 => Yii::t('main', 'иные категории граждан, определяемые законами Республики Казахстан'),
            28 => Yii::t('main', 'иные категории граждан, определяемые по решению Правительства Республики Казахстан'),
            29 => Yii::t('main', 'не относится ни к одной из вышеперечисленных категорий'),
        ];
    }

    /**
     * Возвращает роли для журнала оценок
     * @return string[]
     * @author Alexander Mityukhin  <almittt@mail.ru>
     */
    public static function getJournalRoles(): array
    {
        $journalRoles[OrganizationRoles::CONCERTMASTER] = Yii::t('main', 'Концертмейстер');
        $journalRoles[OrganizationRoles::TEACHER] = Yii::t('main', 'Преподаватель');

        return $journalRoles;
    }

}
