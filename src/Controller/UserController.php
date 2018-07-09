<?php
	namespace App\Controller;

	use DateTime;
	use Exception;
	use Symfony\Component\Form\Extension\Core\Type\HiddenType;
	use Symfony\Component\Routing\Annotation\Route;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpFoundation\JsonResponse;
	use Symfony\Component\Serializer\Serializer;
	use FOS\RestBundle\Controller\Annotations as FOSRest;
	use Symfony\Component\Form\Extension\Core\Type\DateType;
	use App\Entity\User;

	/**
	* User controller
	* @Route("/api")
	*/

	class UserController extends Controller {
		/**
		* Lists all users
		* @FOSRest\Get("/user")
		*
		* @return json
		*/
		public function getUserAction() {
			try {
				$serializer = $this->container->get('jms_serializer');
				$repository = $this->getDoctrine()->getRepository(User::class);
				$user = $repository->findall();

				$response['Response'] = Response::HTTP_OK;
				$response['Message'] = $serializer->serialize($user, 'json');

				return new JsonResponse($response);
			} catch(Exception $ex) {
				$response['Response'] = Response::HTTP_INTERNAL_SERVER_ERROR;
				$response['Message'] = "There was an error: " . $ex->getMessage();
				return new JsonResponse($response);
			}
		}

		/**
		* Create user.
		* @FOSRest\Post("/user")
		*
		* @return json
		*/
		public function postUserAction(Request $request) {
			try {
				$user = new User();
				$user->setName($request->get('name'));
				$em = $this->getDoctrine()->getManager();
				$em->persist($user);
				$em->flush();

				$response['Response'] = Response::HTTP_CREATED;
				$response['Message'] = "User inserted. Id: " . $user->getId();

				return new JsonResponse($response);
			} catch(Exception $ex) {
				$response['Response'] = Response::HTTP_INTERNAL_SERVER_ERROR;
				$response['Message'] = "There was an error: " . $ex->getMessage();
				return new JsonResponse($response);
			}
		}

		/**
		* Delete user.
		* @FOSRest\Delete("/user/delete/{id}")
		*
		* @return json
		*/
		public function deleteUserAction($id) {
			try {
				$em = $this->getDoctrine()->getManager();
				$user = $em->getRepository(User::class)->find($id);

				if(!$user) {
					throw new Exception('No user found for ID ' . $id);
				}

				if(!$user->getActive()) {
					throw new Exception('User ' . $user->getName() . ' has already been deleted.');
				}

				$user->setActive(0);
				$user->setDeleteDate(new DateTime());
				$em->flush();

				$response['Response'] = Response::HTTP_OK;
				$response['Message'] = "User deleted. Id: " . $id;

				return new JsonResponse($response);
			} catch(Exception $ex) {
				$response['Response'] = Response::HTTP_INTERNAL_SERVER_ERROR;
				$response['Message'] = "There was an error: " . $ex->getMessage();
				return new JsonResponse($response);
			}
		}

	}
