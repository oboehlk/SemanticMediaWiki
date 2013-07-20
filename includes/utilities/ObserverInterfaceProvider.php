<?php

namespace SMW;

/**
 * Contains all interfaces and implementation classes to
 * enable a Observer-Subject (or Publisher-Subcriber) pattern where
 * objects can indepentanly be notfied about a state change and initiate
 * an update of its registered Publisher
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * Interface describing a Subsriber
 *
 * @ingroup Observer
 */
interface Subscriber {

	/**
	 * Receive update from a Publisher
	 *
	 * @since  1.9
	 *
	 * @param Publisher $publisher
	 */
	public function update( Publisher $publisher );

}

/**
 * Interface describing a Publisher
 *
 * @ingroup Observer
 */
interface Publisher {

	/**
	 * Attach an Subscriber
	 *
	 * @since  1.9
	 *
	 * @param Subscriber $subscriber
	 */
	public function attach( Subscriber $subscriber );

	/**
	 * Detach an Subscriber
	 *
	 * @since  1.9
	 *
	 * @param Subscriber $subscriber
	 */
	public function detach( Subscriber $subscriber );

	/**
	 * Notify an Subscriber
	 *
	 * @since  1.9
	 */
	public function notify();

}

/**
 * Implement the Subsriber interface resutling in an Observer base class
 * that accomodates necessary methods to update an invoked publisher
 *
 * @ingroup Observer
 */
abstract class Observer implements Subscriber {

	/**
	 * @since  1.9
	 *
	 * @param Publisher|null $subject
	 */
	public function __construct( Publisher $subject = null ) {
		if ( $subject instanceof Publisher ) {
			$subject->attach( $this );
		}
	}

	/**
	 * Update handling of an invoked publisher by relying
	 * on the state object to carry out the task
	 *
	 * @since 1.9
	 *
	 * @param Publisher|null $subject
	 */
	public function update( Publisher $subject ) {

		if ( method_exists( $this, $subject->getState() ) ) {
			call_user_func_array( array( $this, $subject->getState() ), array( $subject ) );
		}
	}
}

/**
 * Implement the Publisher interface resulting in an Subject base class
 *
 * @ingroup Observer
 */
abstract class Subject implements Publisher {

	/** @var Subscriber[] */
	protected $observers = array();

	/** string */
	protected $state = null;

	/**
	 * @see Publisher::attach
	 *
	 * @since 1.9
	 *
	 * @param Subscriber $observer
	 */
	public function attach( Subscriber $observer ) {
		if ( $this->observers === array() || !isset( $this->observers[$observer] ) ) {
			$this->observers[] = $observer;
		}
	}

	/**
	 * @since  1.9
	 *
	 * @param Subscriber $observer
	 */
	public function detach( Subscriber $observer ) {
		if ( $this->observers !== array() && isset( $this->observers[$observer] ) ) {
			unset( $this->observers[$observer] );
		}
	}

	/**
	 * Returns a string which represents an updateable
	 * Publisher object method
	 *
	 * @since 1.9
	 *
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * Set a state variable state and initiates to notify
	 * the attached Subscribers
	 *
	 * @since 1.9
	 *
	 * @param string $state
	 */
	public function setState( $state ) {
		$this->state = $state;
		$this->notify();
	}

	/**
	 * Notifies the updater of all invoked Subscribers
	 *
	 * @since  1.9
	 */
	public function notify() {

		if ( $this->observers !== array() ) {
			foreach ( $this->observers as $observer ) {
				$observer->update( $this );
			}
		}
	}

	/**
	 * Returns registered Observers
	 *
	 * @since 1.9
	 *
	 * @return arrau
	 */
	public function getObservers() {
		return $this->observers;
	}

}