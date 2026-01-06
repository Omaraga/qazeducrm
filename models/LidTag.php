<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;

/**
 * Модель тегов лидов
 *
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property string $color
 * @property string $icon
 * @property int $sort_order
 * @property bool $is_system
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property LidTagRelation[] $lidTagRelations
 * @property Lids[] $lids
 */
class LidTag extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Предустановленные цвета
    const COLORS = [
        'gray' => 'Серый',
        'red' => 'Красный',
        'orange' => 'Оранжевый',
        'amber' => 'Янтарный',
        'yellow' => 'Жёлтый',
        'lime' => 'Лаймовый',
        'green' => 'Зелёный',
        'emerald' => 'Изумрудный',
        'teal' => 'Бирюзовый',
        'cyan' => 'Голубой',
        'sky' => 'Небесный',
        'blue' => 'Синий',
        'indigo' => 'Индиго',
        'violet' => 'Фиолетовый',
        'purple' => 'Пурпурный',
        'fuchsia' => 'Фуксия',
        'pink' => 'Розовый',
        'rose' => 'Розовато-красный',
    ];

    // Предустановленные иконки
    const ICONS = [
        'tag' => 'Метка',
        'star' => 'Звезда',
        'fire' => 'Огонь',
        'bolt' => 'Молния',
        'heart' => 'Сердце',
        'flag' => 'Флаг',
        'bookmark' => 'Закладка',
        'check-circle' => 'Галочка',
        'exclamation-circle' => 'Внимание',
        'clock' => 'Часы',
        'user' => 'Пользователь',
        'phone' => 'Телефон',
        'chat-bubble-left' => 'Сообщение',
        'currency-dollar' => 'Деньги',
        'academic-cap' => 'Образование',
        'trophy' => 'Трофей',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%lid_tags}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['organization_id', 'sort_order', 'is_deleted'], 'integer'],
            [['is_system'], 'boolean'],
            [['name'], 'string', 'max' => 100],
            [['color'], 'string', 'max' => 20],
            [['icon'], 'string', 'max' => 50],
            ['color', 'default', 'value' => 'gray'],
            ['icon', 'default', 'value' => 'tag'],
            ['sort_order', 'default', 'value' => 0],
            ['is_system', 'default', 'value' => false],
            [['name'], 'unique', 'targetAttribute' => ['organization_id', 'name'], 'message' => 'Тег с таким названием уже существует'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => 'Организация',
            'name' => 'Название',
            'color' => 'Цвет',
            'icon' => 'Иконка',
            'sort_order' => 'Порядок',
            'is_system' => 'Системный',
            'is_deleted' => 'Удалён',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
        ];
    }

    /**
     * Связь с таблицей связей
     */
    public function getLidTagRelations()
    {
        return $this->hasMany(LidTagRelation::class, ['tag_id' => 'id']);
    }

    /**
     * Связь с лидами через таблицу связей
     */
    public function getLids()
    {
        return $this->hasMany(Lids::class, ['id' => 'lid_id'])
            ->via('lidTagRelations');
    }

    /**
     * Количество лидов с этим тегом
     */
    public function getLidsCount(): int
    {
        return $this->getLidTagRelations()->count();
    }

    /**
     * Получить все теги организации
     */
    public static function getOrganizationTags(): array
    {
        return self::find()
            ->byOrganization()
            ->notDeleted()
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();
    }

    /**
     * Получить теги для dropdown
     */
    public static function getTagsForDropdown(): array
    {
        $tags = self::getOrganizationTags();
        $result = [];
        foreach ($tags as $tag) {
            $result[$tag->id] = $tag->name;
        }
        return $result;
    }

    /**
     * Создать стандартные теги для организации
     */
    public static function createDefaults(int $organizationId): void
    {
        $defaults = [
            ['name' => 'Горячий', 'color' => 'orange', 'icon' => 'fire', 'is_system' => true],
            ['name' => 'VIP', 'color' => 'purple', 'icon' => 'star', 'is_system' => true],
            ['name' => 'Повторный', 'color' => 'blue', 'icon' => 'arrow-path', 'is_system' => true],
            ['name' => 'Не отвечает', 'color' => 'gray', 'icon' => 'phone-x-mark', 'is_system' => true],
        ];

        foreach ($defaults as $data) {
            // Проверяем, не существует ли уже
            $exists = self::find()
                ->andWhere(['organization_id' => $organizationId, 'name' => $data['name']])
                ->exists();

            if (!$exists) {
                $tag = new self();
                $tag->organization_id = $organizationId;
                $tag->name = $data['name'];
                $tag->color = $data['color'];
                $tag->icon = $data['icon'];
                $tag->is_system = $data['is_system'];
                $tag->save(false);
            }
        }
    }

    /**
     * Конвертировать в массив для JSON
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_system' => (bool)$this->is_system,
            'lids_count' => $this->getLidsCount(),
        ];
    }
}
