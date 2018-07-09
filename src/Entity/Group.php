<?php
	namespace App\Entity;
	use Doctrine\ORM\Mapping as ORM;
	use Doctrine\Common\Collections\ArrayCollection;
	use JMS\Serializer\Annotation as JMS;
	use App\Entity\User;
	use Exception;

	/**
	* @ORM\Entity
	* @ORM\Table(name="groups")
	*/

	class Group {

		/**
		* @ORM\Id
		* @ORM\Column(type="integer")
		* @ORM\GeneratedValue(strategy="AUTO")
		*/
		private $id;
		/**
		* @ORM\Column(type="string", length=100)
		*/
		private $name;
		/**
		* @ORM\Column(type="boolean")
		*/
		private $active = 1;
		/**
		* @ORM\Column(type="datetime", nullable=true)
		*/
		private $delete_date = null;

		/**
		 * @ORM\ManyToMany(targetEntity="User", inversedBy="groups")
     	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
		 */
		private $users;

		public function __construct() {
			$this->users = new ArrayCollection();
		}

		public function setId($id) {
			$this->id = $id;
		}

		public function getId() {
			return $this->id;
		}

		public function setName($name) {
			$this->name = $name;	
		}

		public function getName() {
			return $this->name;	
		}

		public function setActive($active) {
			$this->active = $active;
		}

		public function getActive() {
			return $this->active;
		}

		public function setDeleteDate($delete_date) {
			$this->delete_date = $delete_date;
		}

		public function getDeleteDate() {
			return $this->delete_date;		
		}

		public function addUser(User $user) {
			if($this->users->contains($user) === true) {
				return false;
			}

			$this->users->add($user);
			$user->addGroup($this);

			return true;
		}

		public function removeUser(User $user) {
			if($this->users->contains($user) === false) {
				return false;
			}

			$this->users->removeElement($user);
			$user->removeGroup($this);

			return true;
		}

		public function getUser() {
			return $this->users;
		}

		public function setUsers($users) {
			$this->users = $users;

			return $this;
		}
	}