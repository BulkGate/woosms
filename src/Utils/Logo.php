<?php declare(strict_types=1);

namespace BulkGate\WooSms\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;

class Logo
{
	use Strict;

	public const Menu = 'data:image/svg+xml;base64,PHN2ZyBpZD0ic3ZnNDA1OCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgNDEuMyAzMS43NCI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiM5Y2EyYTc7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZT5sb2dvPC90aXRsZT48ZyBpZD0ibGF5ZXIxIj48ZyBpZD0iZzM0MTgiPjxwYXRoIGlkPSJwYXRoMzQyMCIgY2xhc3M9ImNscy0xIiBkPSJNMTUwLjA2LDE3LjExYzYuNzUsNC42MiwxMS40NywxMC41NSwxMy40MSwyNC4xN2g3LjI0QzE3MCwyNy43LDE2MS44NSwxNiwxNTAuMDYsOS41NCwxMzguMjgsMTYsMTMwLjE3LDI3LjcsMTI5LjQxLDQxLjI4aDcuMjVzMTIuODMuNDgsMjEuMzEtMTIuODdjMCwwLTguNTIsNC41My0xMy41MywyLjY1LTQuNzQtMS43OC0yLjQ1LTUuODktMi4xOS02LjMzYTI4LjI5LDI4LjI5LDAsMCwxLDcuODEtNy42MiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTEyOS40MSAtOS41NCkiLz48L2c+PC9nPjwvc3ZnPg==';

	public const CssLoader = <<<'CSS'
		@keyframes logo {
		    0% {
		        filter: grayscale(1) opacity(.2);
		        transform: scale(.6);
		    }
		    25% {
		        filter: none;
		        transform: scale(.65);
		    }
		    70% {
		        transform: none;
		    }
		}
		
		@keyframes heading {
		    0% {
		        opacity: .1;
		    }
		    50% {
		        opacity: .8;
		    }
		    100% {
		        opacity: 1;
		    }
		}
		
		@keyframes progress {
		    100% {
		        opacity: 1
		    }
		}
		
		@keyframes progress-processing {
		    0% {
		        transform: translateX(-300px)
		    }
		    5% {
		        transform: translateX(-240px)
		    }
		    15% {
		        transform: translateX(-30px)
		    }
		    25% {
		        transform: translateX(-30px)
		    }
		    30% {
		        transform: translateX(-20px)
		    }
		    45% {
		        transform: translateX(-20px)
		    }
		    50% {
		        transform: translateX(-15px)
		    }
		    65% {
		        transform: translateX(-15px)
		    }
		    70% {
		        transform: translateX(-10px)
		    }
		    95% {
		        transform: translateX(-10px)
		    }
		    100% {
		        transform: translateX(-5px)
		    }
		}
		
		#bulkgate-plugin {
		    position: relative;
		    z-index: 0;
		    margin-left: calc(var(--bulkgate-plugin-body-indent, 0) * -1);
		}
		
		#bulkgate-plugin #loading {
		    position: fixed;
		    contain: layout;
		    left: 0;
		    top: 0;
		    right: 0;
		    bottom: 0;
		    background: #fff;
		    z-index: 2999;
		    display: flex;
		    align-items: center;
		    justify-content: center;
		    text-align: center;
		}
		
		#bulkgate-plugin #loading img {
		    width: 160px;
		    animation: logo 1.5s .3s both;
		    margin: 24px 0;
		}
		
		#bulkgate-plugin #loading h3 {
		    font-size: 32px;
		    color: #606469;
		    animation: heading .5s .675s both;
		}
		
		#bulkgate-plugin #progress {
		    animation: progress .5s 2.5s 1 both;
		    height: 4px;
		    width: 100%;
		    opacity: 0;
		    background: #ddd;
		    position: relative;
		    overflow: hidden;
		}
		
		#bulkgate-plugin #progress:before {
		    animation: progress-processing 20s 3s linear both;
		    background-color: var(--secondary);
		    content: '';
		    display: block;
		    height: 100%;
		    position: absolute;
		    transform: translateX(-300px);
		    width: 100%;
		}
		
		gate-ecommerce-plugin {
		    box-sizing: border-box; /* realne se tyka pouze web-componenty */
		}
	CSS;
}
