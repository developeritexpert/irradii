<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Exception;

/**
 * This is the model class for table "user_oauth".
 *
 * The followings are the available columns in table 'user_oauth':
 * @property integer $user_id
 * @property string $provider name of provider
 * @property string $identifier unique user authentication id that was returned by provider
 * @property string $profile_cache
 * @property string $session_data session data with user profile
 *
 *
 * @version 1.2.5
 * @copyright Copyright &copy; 2013 Sviatoslav Danylenko
 * @author Sviatoslav Danylenko <dev@udf.su> 
 * @license MIT ({@link http://opensource.org/licenses/MIT})
 * @link https://github.com/SleepWalker/hoauth
 */
class UserOAuth extends ActiveRecord
{
    /**
     * @var $_hybridauth HybridAuth class instance
     */
    protected $_hybridauth;

    /**
     * @var $_adapter HybridAuth adapter    
     */
    protected $_adapter;

    /**
     * @var $_profileCache property for holding of unserialized 
     *        profile cache copy
     */
    protected $_profileCache;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        if(!empty(Yii::$app->db->tablePrefix))
            return '{{%user_oauth}}';
        else
            return 'user_oauth';
    }

    /**
     * {@inheritdoc}
     */
    public static function find()
    {
        try {
            $model = parent::find();
            
            // db updates 'on the fly'
            $instance = new static;
            $instance->updateDb();
            
            return $model;
        } catch (\yii\db\Exception $e) {
            self::createDbTable();
            Yii::$app->controller->refresh();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'provider', 'identifier'], 'required'],
            [['user_id'], 'integer'],
            [['profile_cache', 'session_data'], 'safe'],
            [['provider'], 'string', 'max' => 45],
            [['identifier'], 'string', 'max' => 64],
            [['provider', 'identifier'], 'unique', 'targetAttribute' => ['provider', 'identifier']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'provider' => 'Provider',
            'identifier' => 'Identifier',
            'profile_cache' => 'Profile Cache',
            'session_data' => 'Session Data',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind()
    {
        parent::afterFind();

        if(!empty($this->profile_cache))
            $this->_profileCache = (object)unserialize($this->profile_cache);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if(!empty($this->_profileCache))
            $this->profile_cache = serialize((array)$this->_profileCache);

        return parent::beforeSave($insert);
    }

    /**
     * @static
     * @access public
     * @return configuration array of HybridAuth lib
     */
    public static function getConfig()
    {
        $config = self::getConfigPath();

        if(!file_exists($config))
            throw new Exception("The config.php file doesn't exists");

        return require($config);
    }

    /**
     * @return path to the HybridAuth config file
     */
    public static function getConfigPath()
    {
        $config = isset(Yii::$app->params['hoauth']['configAlias']) ? Yii::$app->params['hoauth']['configAlias'] : '';
        
        if(empty($config))
        {
            $yiipath = Yii::getAlias('@app/config/hoauth');
            $config = $yiipath . '.php';
        }
        else if(strpos($config, DIRECTORY_SEPARATOR)===false)
        {
            $config = Yii::getAlias('@' . $config) . '.php';
        }

        return $config;
    }
    
    /**
     * @access public
     * @param integer $user_id
     * @param string|boolean $provider
     * @return array|UserOAuth|null
     */
    public function findUser($user_id, $provider = false)
    {
        $query = self::find()->where(['user_id' => $user_id]);
        
        if($provider)
        {
            $query->andWhere(['provider' => $provider]);
            return $query->one();
        }
        else
            return $query->all();
    }

    /**
     * @access public
     * @return Auth class. With restored users authentication session data
     * @link http://hybridauth.sourceforge.net/userguide.html
     * @link http://hybridauth.sourceforge.net/userguide/HybridAuth_Sessions.html
     */
    public function getHybridAuth()
    {
        if(!isset($this->_hybridauth))
        {
            $path = Yii::getAlias('@app/vendor/hybridauth/hybridauth/hybridauth');


            require_once($path . '/Hybrid/Auth.php');
            $this->_hybridauth = new \Hybrid_Auth(self::getConfig());

            if(!empty($this->session_data))
                $this->_hybridauth->restoreSessionData($this->session_data);
        }

        return $this->_hybridauth;
    }

    /**
     * @access public
     * @return Adapter for current provider or null, when we have no session data.
     * @link http://hybridauth.sourceforge.net/userguide.html
     */
    public function getAdapter()
    {
        if(!isset($this->_adapter) && isset($this->session_data) && isset($this->provider))
            $this->_adapter = $this->hybridAuth->getAdapter($this->provider);

        return $this->_adapter;
    }

    /**
     * authenticates user by specified adapter    
     * 
     * @param string $provider 
     * @access public
     * @return UserOAuth|null
     */
    public function authenticate($provider)
    {
        if(empty($this->provider))
        {
            try
            {
                $this->_adapter = $this->hybridauth->authenticate($provider);
                $this->identifier = $this->profile->identifier;
                $this->provider = $provider;
                $oAuth = self::find()->where([
                    'provider' => $this->provider, 
                    'identifier' => $this->identifier
                ])->one();
                
                if($oAuth)
                    $this->setAttributes($oAuth->attributes, false);
                else
                    $this->isNewRecord = true;

                $this->session_data = $this->hybridauth->getSessionData();
                return $this;
            }
            catch( \Exception $e )
            {
                $error = "";
                switch( $e->getCode() )
                {
                    case 6 : //$error = "User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again."; 
                    case 7 : //$error = "User not connected to the provider."; 
                    $this->logout();
                    return $this->authenticate($provider);
                    break;
                }
                throw $e;
            }
        }

        return null;
    }

    /**
     * Breaks HybridAuth session and logs user from sn out.
     *
     * @access public
     */
    public function logout()
    {
        if(!empty($this->_adapter))
        {
            $this->_adapter->logout();
            $this->_adapter = null;
            $this->_hybridauth = null;
            $this->provider = null;
            $this->identifier = null;
            $this->session_data = null;
            $this->_profileCache = null;
        }
    }

    /**
     * @access public
     * @return \Hybrid_User_Profile user social profile object
     */
    public function getProfile()
    {
        $profile = $this->adapter->getUserProfile();
        //caching profile
        $this->_profileCache = $profile;

        return $profile;
    }

    /**
     * binds local user to current provider 
     * 
     * @param mixed $user_id id of the user
     * @access public
     * @return whether the model successfully saved
     */
    public function bindTo($user_id)
    {
        $this->user_id = $user_id;
        return $this->save();
    }

    /**
     * @access public
     * @return whether this social network account bond to existing local account
     */
    public function getIsBond()
    {
        return !empty($this->user_id);
    }

    /**
     * Getter for cached profile.
     * We implement this method, because in older version of hoauth was no profile cache. So we need to fill db with caches
     * The second reason is camelCase
     */
    public function getProfileCache()
    {
        if(empty($this->_profileCache))
        {
            $this->getProfile();
            $this->save();
        }

        return $this->_profileCache;
    }

    /**
     * creates table for holding provider bindings    
     */
    protected static function createDbTable()
    {
        $sql = file_get_contents(dirname(__FILE__) . '/user_oauth.sql');
        $sql = strtr($sql, [
            '{{user_oauth}}' => Yii::$app->db->tablePrefix . 'user_oauth'
        ]);
        Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * Runs DB updates on the fly
     */
    protected function updateDb()
    {
        $updates = [];
        $tableName = $this->tableName();
        $db = Yii::$app->db;
        $schema = $db->schema;
        
        // Check if provider column exists (for old versions)
        try {
            $this->provider = $this->provider;
        } catch(\Exception $e) {
            $sql = "ALTER TABLE `{$tableName}` CHANGE `name` `provider` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                    CHANGE `value` `identifier` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
            $updates[] = $sql;
        }

        // Check if profile_cache column exists (for versions < 1.2.2)
        try {
            $this->profile_cache = $this->profile_cache;
        } catch(\Exception $e) {
            $sql = "ALTER TABLE `{$tableName}` ADD `profile_cache` TEXT NOT NULL AFTER `identifier`";
            $updates[] = $sql;
        }

        if(count($updates))
        {
            foreach($updates as $sql)
            {
                $db->createCommand($sql)->execute();
            }
            Yii::$app->controller->refresh();
        }
    }

    /**
     * @return array relational rules.
     */
    public function getRelations()
    {
        return [
            'user' => $this->hasOne(User::class, ['id' => 'user_id']),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}